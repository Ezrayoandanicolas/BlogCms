import json
import os
import subprocess
import sys
from datetime import datetime, timedelta

from api_client import BlogCMSClient
from config import Config
from image_generator import generate_image_from_prompt
from ollama_client import OllamaClient
from prompt_templates import (
    OUTLINE_PROMPT,
    CONTENT_PROMPT,
    TAGS_PROMPT,
    IMAGE_PROMPT_SYSTEM,
    TOPIC_EXTRACTION_PROMPT,
)


class ArticleGenerator:
    def __init__(self, cms: BlogCMSClient = None, topic: str = ""):
        self.ollama = OllamaClient(Config.OLLAMA_URL, Config.OLLAMA_MODEL)
        self.cms = cms or BlogCMSClient(
            Config.BLOGCMS_URL,
            Config.BLOGCMS_EMAIL,
            Config.BLOGCMS_PASSWORD,
            Config.BLOGCMS_TOKEN,
            api_key=Config.BLOGCMS_API_KEY,
        )
        if not topic:
            settings = self.cms.get_site_settings()
            topic = settings.get("site_topic", "") or Config.BLOGCMS_TOPIC
        self.topic = topic

    @staticmethod
    def _extract_points(points: list) -> list[str]:
        result = []
        for p in points:
            if isinstance(p, str):
                result.append(p)
            elif isinstance(p, dict):
                for v in p.values():
                    if isinstance(v, str):
                        result.append(v)
                        break
        return result

    def _fmt(self, template: str) -> str:
        return template.replace("{topic}", self.topic)

    @property
    def _domain_url(self) -> str:
        return self.cms.base_url

    @staticmethod
    def _ensure_html_format(content: str) -> str:
        if not content:
            return ""
        if "<p>" in content or "<h2>" in content or "<h1>" in content or "<h3>" in content:
            return content
        lines = [l.strip() for l in content.strip().split("\n") if l.strip()]
        if not lines:
            return content
        html_parts = []
        in_list = False
        for line in lines:
            if line.startswith("# "):
                html_parts.append(f"<h1>{line[2:]}</h1>")
            elif line.startswith("## "):
                html_parts.append(f"<h2>{line[3:]}</h2>")
            elif line.startswith("### "):
                html_parts.append(f"<h3>{line[4:]}</h3>")
            elif line.startswith("- ") or line.startswith("* ") or line[0].isdigit() and line[1:3] in (". ", ") "):
                item = line[3:] if line[1] == "." or line[1] == ")" else line[2:]
                if not in_list:
                    html_parts.append("<ul>")
                    in_list = True
                html_parts.append(f"<li>{item}</li>")
            else:
                if in_list:
                    html_parts.append("</ul>")
                    in_list = False
                if line and not line.startswith("<"):
                    html_parts.append(f"<p>{line}</p>")
                else:
                    html_parts.append(line)
        if in_list:
            html_parts.append("</ul>")
        return "\n".join(html_parts)

    def generate_article(self, topic: str = "", image_prompt_override: str = "", published_at: str = "") -> dict:
        active_topic = topic or self.topic
        print(f"\n{'='*60}")
        print(f"📝 Niche: {active_topic}")
        print(f"{'='*60}")

        # === LANGKAH 1: Judul + Outline ===
        print("\n1️⃣  Generate judul & outline...")
        outline_data = self.ollama.generate_json(
            messages=[
                {"role": "user", "content": f"Buat outline artikel tentang: {active_topic}"}
            ],
            system=self._fmt(OUTLINE_PROMPT),
            temperature=0.7,
        )
        title = outline_data.get("title", "Untitled")
        category_name = outline_data.get("category", "Umum")
        outline = outline_data.get("outline", [])
        print(f"   Judul: {title}")
        print(f"   Kategori: {category_name}")
        print(f"   Sub judul: {len(outline)} bagian")

        # === LANGKAH 2: Konten ===
        print("\n2️⃣  Generate konten berdasarkan judul & outline...")
        outline_text = "\n".join(
            f"- {s.get('subtitle', '')}: {'; '.join(self._extract_points(s.get('points', [])))}"
            for s in outline
        )
        content_data = self.ollama.generate_json(
            messages=[
                {
                    "role": "user",
                    "content": f"Judul: {title}\n\nOutline:\n{outline_text}",
                }
            ],
            system=self._fmt(CONTENT_PROMPT),
            temperature=0.7,
        )
        content = content_data.get("content", "")
        excerpt = content_data.get("excerpt", "")

        content = self._ensure_html_format(content)

        if not excerpt and content:
            import re
            stripped = re.sub(r'<[^>]+>', '', content).strip()
            excerpt = ' '.join(stripped.split()[:30]) + '...'

        # Jika konten terlalu pendek, regenerate dengan perintah lebih tegas
        if len(content.split()) < 300:
            print(f"   ⚠️ Konten terlalu pendek ({len(content.split())} kata), regenerate...")
            content_data = self.ollama.generate_json(
                messages=[
                    {
                        "role": "user",
                        "content": f"Judul: {title}\n\nOutline:\n{outline_text}",
                    },
                    {
                        "role": "assistant",
                        "content": content,
                    },
                    {
                        "role": "user",
                        "content": "Konten di atas terlalu pendek. TULIS ULANG dengan minimal 800 kata. Kembangkan setiap poin outline menjadi 2-3 paragraf. Berikan contoh dan detail."
                    }
                ],
                system=self._fmt(CONTENT_PROMPT),
                temperature=0.8,
            )
            content = content_data.get("content", content)
            excerpt = content_data.get("excerpt", excerpt)

            content = self._ensure_html_format(content)

        print(f"   Panjang konten: {len(content.split())} kata ({len(content)} karakter)")

        # === LANGKAH 3: Tags ===
        print("\n3️⃣  Generate tags dari konten...")
        tags_data = self.ollama.generate_json(
            messages=[
                {
                    "role": "user",
                    "content": f"Judul: {title}\n\nKonten:\n{content[:1000]}...",
                }
            ],
            system=self._fmt(TAGS_PROMPT),
            temperature=0.3,
        )
        tags = tags_data.get("tags", [])
        print(f"   Tags: {', '.join(tags)}")

        # === LANGKAH 4: Image prompt ===
        image_prompt = image_prompt_override
        if not image_prompt:
            print("\n4️⃣  Generate prompt gambar...")
            image_prompt = self.ollama.chat(
                messages=[{"role": "user", "content": f"Judul artikel: {title}\n\nExcerpt: {excerpt}"}],
                system=self._fmt(IMAGE_PROMPT_SYSTEM),
                temperature=0.6,
            )
            image_prompt = image_prompt.strip().strip('"').strip("'")
        print(f"   Image prompt: {image_prompt[:80]}...")

        # === PUBLISH ===
        print("\n📤 Publishing ke BlogCMS...")

        # Cari/buat kategori
        print(f"   📂 Kategori: {category_name}")
        categories = self.cms.get_categories()
        existing = [c for c in categories if c["name"].lower() == category_name.lower()]
        if existing:
            category_id = existing[0]["id"]
        else:
            cat = self.cms.create_category(category_name)
            category_id = cat["id"]

        # Generate & upload image
        featured_image_url = ""
        if image_prompt:
            print("   🎨 Generate featured image dengan SD...")
            image_path = generate_image_from_prompt(image_prompt)
            if image_path:
                print("   📤 Upload image...")
                featured_image_url = self.cms.upload_image(image_path)
                print(f"   ✅ Image: {featured_image_url}")
            else:
                print("   ⚠️ Gagal generate image")

        # Publish
        pub_str = f" → {published_at}" if published_at else ""
        print(f"   📝 Publish artikel{pub_str}...")
        post = self.cms.create_post(
            title=title,
            content=content,
            excerpt=excerpt,
            category_id=category_id,
            featured_image=featured_image_url,
            status="published",
            tags=tags,
            published_at=published_at,
        )
        print(f"\n✅ Artikel dipublikasikan!")
        print(f"   ID: {post['id']} - {post['title']}")
        print(f"   🔗 {self._domain_url}/blog/{post['slug']}")
        return post

    def add_domains(self, domains: list[str], port: int = 0):
        print(f"\n🏗️  Menambahkan {len(domains)} domain baru...")
        print(f"   BlogCMS: {Config.BLOGCMS_URL}\n")

        created = self.cms.create_domains(domains)

        print(f"{'='*60}")
        print(f"✅ {len(created)} domain berhasil dibuat!")
        print(f"{'='*60}")

        cf_tunnel_id = Config.CLOUDFLARE_TUNNEL_ID
        cf_email = Config.CLOUDFLARE_EMAIL
        cf_api_key = Config.CLOUDFLARE_API_KEY
        use_tunnel = port > 0 and cf_tunnel_id and cf_email and cf_api_key

        if use_tunnel:
            print(f"\n🔧 Mode Cloudflare Tunnel aktif (port: {port})")
            script_path = os.path.join(os.path.dirname(__file__), "add-tunnel-cli.php")
            if not os.path.exists(script_path):
                print(f"   ⚠️ Script {script_path} tidak ditemukan, skip tunnel")
                use_tunnel = False

        for dom in created:
            host = dom['host']

            # Skip tunnel untuk domain localhost (tanpa titik)
            if use_tunnel and '.' not in host:
                print(f"\n   ⏭️  {host} bukan domain publik, skip tunnel")
                use_tunnel_this = False
            else:
                use_tunnel_this = use_tunnel

            print(f"\n🏠 {host}")
            print(f"   Theme: {dom['theme_slug']}")
            print(f"   ID   : {dom['id']}")
            print()
            print(f"   🤖 Generate site_name, description, topic via Ollama...")

            settings_data = self.ollama.generate_json(
                messages=[{
                    "role": "user",
                    "content": f"Buat identitas untuk website blog dengan domain {host} yang menggunakan tema '{dom['theme_slug']}'. "
                               f"Buat site_name yang menarik, site_description (1-2 kalimat), dan site_topic (niche/kategori utama). "
                               f"Kembalikan JSON: site_name, site_description, site_topic."
                }],
                temperature=0.8,
            )

            site_name = settings_data.get("site_name", host)
            site_desc = settings_data.get("site_description", "")
            site_topic = settings_data.get("site_topic", "")

            print(f"   Name      : {site_name}")
            print(f"   Desc      : {site_desc[:60]}...")
            print(f"   Topic     : {site_topic}")

            print(f"   💾 Simpan settings ke BlogCMS...")
            self.cms.update_settings(dom["id"], {
                "site_name": site_name,
                "site_description": site_desc,
                "site_topic": site_topic,
            })
            print(f"   ✅ Settings tersimpan")

            if use_tunnel_this:
                service_url = f"http://localhost:{port}"
                print(f"\n   🔧 Setup Cloudflare Tunnel...")
                print(f"   Hostname: {host}")
                print(f"   Service : {service_url}")
                print(f"   Tunnel  : {cf_tunnel_id}")

                result = subprocess.run(
                    ["php", script_path, "--hostname", host, "--service", service_url],
                    capture_output=True, text=True, timeout=30,
                    encoding='utf-8', errors='replace',
                )

                out = result.stdout.strip() if result.stdout else ""
                err = result.stderr.strip() if result.stderr else ""

                if err:
                    for line in err.split("\n"):
                        line = line.strip()
                        if line:
                            print(f"   {line}")

                if out:
                    try:
                        data = json.loads(out)
                        if data.get("success"):
                            print(f"   [OK] Cloudflare tunnel: {data['data']['hostname']} -> {data['data']['service']}")
                        else:
                            print(f"   [FAIL] {data.get('message', 'Unknown')}")
                    except json.JSONDecodeError:
                        print(f"   {out}")
                elif result.returncode != 0:
                    print(f"   [FAIL] PHP script exit code: {result.returncode}")

        print(f"\n{'='*60}")
        print("✅ Semua domain siap digunakan!")
        for d in created:
            print(f"   - {d['host']} (ID: {d['id']}, theme: {d['theme_slug']})")
        return created

    def generate_topics(self, count: int = 5) -> list[str]:
        print("⏳ Generate ide artikel...")
        try:
            result = self.ollama.generate_json(
                messages=[{"role": "user", "content": f"Buatkan {count} ide judul artikel untuk niche {self.topic}. Kembalikan hanya array JSON string saja, tanpa objek."}],
                system=self._fmt(TOPIC_EXTRACTION_PROMPT),
            )
            if isinstance(result, list):
                topics = [str(i) for i in result if i]
                if topics:
                    return topics
            if isinstance(result, dict):
                topics = [str(v) for v in result.values() if isinstance(v, str) and v.strip()]
                if topics:
                    return topics
                for v in result.values():
                    if isinstance(v, list):
                        topics = []
                        for item in v:
                            if isinstance(item, str):
                                topics.append(item)
                            elif isinstance(item, dict):
                                for val in item.values():
                                    if isinstance(val, str):
                                        topics.append(val)
                                        break
                        if topics:
                            return topics
        except Exception as e:
            print(f"  ⚠️ Gagal generate topik: {e}")
        return [f"Ide tentang {self.topic}"]

    def run_batch(self, count: int = 3, topic: str = ""):
        print(f"\n🚀 Memulai batch generation: {count} artikel")
        print(f"   Model: {Config.OLLAMA_MODEL}")
        print(f"   Niche: {topic or self.topic}")
        print(f"   BlogCMS: {self._domain_url}\n")

        results = []
        for i in range(count):
            print(f"\n--- Artikel {i+1}/{count} ---")
            post = self.generate_article(topic=topic)
            results.append(post)

        print(f"\n{'='*60}")
        print(f"✅ Selesai! {len(results)} artikel:")
        for r in results:
            print(f"   - {r['title']}: {self._domain_url}/blog/{r['slug']}")
        return results

    def run_daily(self):
        print(f"\n🚀 Daily generation: 1 artikel per domain hari ini")
        print(f"   Model: {Config.OLLAMA_MODEL}")
        print(f"   BlogCMS: {Config.BLOGCMS_URL}\n")

        domains = self.cms.get_domains()
        if not domains:
            print("⚠️ Tidak ada domain aktif ditemukan.")
            return 0

        today = datetime.utcnow().strftime("%Y-%m-%d 08:00:00")
        total = 0

        for domain in domains:
            host = domain["host"]
            domain_url = domain["url"]
            existing = domain["articles_count"]

            if existing > 0 and domain["articles_count"] >= 7:
                print(f"  ⏭️  {host}: sudah {existing} artikel, lewati")
                continue

            print(f"\n{'='*60}")
            print(f"🏠 {host}")
            cms = BlogCMSClient(domain_url, Config.BLOGCMS_EMAIL, Config.BLOGCMS_PASSWORD, Config.BLOGCMS_TOKEN, api_key=Config.BLOGCMS_API_KEY)
            gen = ArticleGenerator(cms=cms)
            print(f"   Topic: {gen.topic}")
            gen.generate_article(published_at=today)
            total += 1

        print(f"\n✅ Selesai! {total} artikel dipublikasikan hari ini.")
        return total

    def run_schedule(self, days: int = 7):
        print(f"\n🚀 Memulai schedule generation: target {days} artikel per domain")
        print(f"   Model: {Config.OLLAMA_MODEL}")
        print(f"   BlogCMS: {Config.BLOGCMS_URL}\n")

        domains = self.cms.get_domains()
        if not domains:
            print("⚠️ Tidak ada domain aktif ditemukan.")
            return 0

        total = 0
        for domain in domains:
            host = domain["host"]
            domain_url = domain["url"]
            existing = domain["articles_count"]
            future = domain["future_scheduled"]
            need = max(0, days - existing)
            already = existing + future

            print(f"{'='*60}")
            print(f"🏠 {host}")
            print(f"   URL    : {domain_url}")

            cms = BlogCMSClient(
                domain_url,
                Config.BLOGCMS_EMAIL,
                Config.BLOGCMS_PASSWORD,
                Config.BLOGCMS_TOKEN,
                api_key=Config.BLOGCMS_API_KEY,
            )

            gen = ArticleGenerator(cms=cms)
            print(f"   Topic  : {gen.topic}")
            print(f"   Artikel: {existing} existing + {future} scheduled = {already} total")

            if need <= 0:
                print(f"   ✅ Sudah {already} artikel, target {days} tercapai. Lewati.")
                print()
                continue

            print(f"   ➕ Perlu {need} artikel lagi")

            last_date = cms.get_last_published_date()
            if last_date:
                try:
                    start = datetime.fromisoformat(last_date.replace("Z", "+00:00"))
                    start = start.replace(tzinfo=None) + timedelta(days=1)
                except ValueError:
                    start = datetime.utcnow()
            else:
                start = datetime.utcnow()

            start = start.replace(hour=8, minute=0, second=0, microsecond=0)
            print(f"   📅 Mulai dari: {start.strftime('%Y-%m-%d')}")

            for i in range(need):
                pub_date = start + timedelta(days=i)
                pub_str = pub_date.strftime("%Y-%m-%d %H:%M:%S")
                print(f"\n--- Artikel {i+1}/{need} ({pub_date.strftime('%Y-%m-%d')}) ---")
                gen.generate_article(published_at=pub_str)
                total += 1

            print()

        print(f"{'='*60}")
        print(f"✅ Selesai! Total {total} artikel terschedule:")
        for d in domains:
            print(f"   - {d['host']}")
        return total
