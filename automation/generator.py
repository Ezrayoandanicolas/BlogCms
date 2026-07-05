import json
import os
import subprocess
import sys
import time
from datetime import datetime, timedelta

from api_client import BlogCMSClient
from config import Config
from image_generator import generate_image_from_prompt
from openrouter_client import OpenRouterClient
from prompt_templates import (
    FULL_ARTICLE_PROMPT,
    IMAGE_PROMPT_SYSTEM,
    TOPIC_EXTRACTION_PROMPT,
)


class ArticleGenerator:
    def __init__(self, cms: BlogCMSClient = None, topic: str = ""):
        self._retry_file = os.path.join(os.path.dirname(__file__), "retry_queue.json")
        self._retry_queue = self._load_retry_queue()
        if Config.OPENROUTER_API_KEY:
            self.llm = OpenRouterClient(Config.OPENROUTER_API_KEY, Config.OPENROUTER_MODEL, Config.OPENROUTER_IMAGE_MODEL, Config.OPENROUTER_FALLBACK_MODELS)
        else:
            from ollama_client import OllamaClient
            self.llm = OllamaClient(Config.OLLAMA_URL, Config.OLLAMA_MODEL)
        self.cms = cms or BlogCMSClient(
            Config.BLOGCMS_URL,
            Config.BLOGCMS_EMAIL,
            Config.BLOGCMS_PASSWORD,
            Config.BLOGCMS_TOKEN,
            api_key=Config.BLOGCMS_API_KEY,
        )
        if not topic:
            try:
                settings = self.cms.get_site_settings()
                topic = settings.get("site_topic", "") or Config.BLOGCMS_TOPIC
            except Exception:
                topic = Config.BLOGCMS_TOPIC
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

    def _process_retry_queue(self):
        if not self._retry_queue:
            return
        remaining = []
        for item in self._retry_queue:
            if item.get("domain_url") != self.cms.base_url:
                remaining.append(item)
                continue
            try:
                cat_name = item.pop("_category_name")
                categories = self.cms.get_categories()
                existing = [c for c in categories if c["name"].lower() == cat_name.lower()]
                if existing:
                    item["category_id"] = existing[0]["id"]
                else:
                    cat = self.cms.create_category(cat_name)
                    item["category_id"] = cat["id"]
                post = self.cms.create_post(**item)
                print(f"   ✅ Retry berhasil: \"{post['title']}\"")
            except Exception as e:
                item["_category_name"] = cat_name
                remaining.append(item)
                print(f"   ⏳ Retry masih gagal, akan dicoba lagi nanti")
        self._retry_queue = remaining
        self._save_retry_queue()

    def _load_retry_queue(self) -> list:
        if os.path.exists(self._retry_file):
            try:
                with open(self._retry_file) as f:
                    return json.load(f)
            except (json.JSONDecodeError, OSError):
                pass
        return []

    def _save_retry_queue(self):
        try:
            with open(self._retry_file, "w") as f:
                json.dump(self._retry_queue, f, indent=2)
        except OSError as e:
            print(f"   ⚠️ Gagal simpan retry queue: {e}")

    @property
    def _image_queue_file(self) -> str:
        return os.path.join(os.path.dirname(__file__), "image_queue.json")

    def _load_image_queue(self) -> list:
        if os.path.exists(self._image_queue_file):
            try:
                with open(self._image_queue_file) as f:
                    return json.load(f)
            except (json.JSONDecodeError, OSError):
                pass
        return []

    def _save_image_queue(self, queue: list):
        try:
            with open(self._image_queue_file, "w") as f:
                json.dump(queue, f, indent=2)
        except OSError as e:
            print(f"   ⚠️ Gagal simpan image queue: {e}")

    def _enqueue_image(self, post: dict, image_prompt: str):
        item = {
            "domain_url": self.cms.base_url,
            "article_id": post["id"],
            "title": post["title"],
            "image_prompt": image_prompt,
        }
        queue = self._load_image_queue()
        queue.append(item)
        self._save_image_queue(queue)
        print(f"   🖼️  Image prompt antri ({post['id']} - {post['title']})")

    def process_image_queue(self):
        queue = self._load_image_queue()
        if not queue:
            print("✅ Tidak ada antrian gambar.")
            return 0

        print(f"\n🖼️  Memproses {len(queue)} gambar dalam antrian...")
        remaining = []
        success = 0

        for item in queue:
            domain_url = item["domain_url"]
            article_id = item["article_id"]
            image_prompt = item["image_prompt"]
            title = item.get("title", f"ID {article_id}")

            cms = BlogCMSClient(domain_url, Config.BLOGCMS_EMAIL, Config.BLOGCMS_PASSWORD, Config.BLOGCMS_TOKEN, api_key=Config.BLOGCMS_API_KEY)

            print(f"\n--- {title} ({domain_url}) ---")
            print(f"   🎨 Generate gambar...")

            try:
                from image_generator import generate_image_from_prompt
                image_path = generate_image_from_prompt(image_prompt)
                if not image_path:
                    print(f"   ⚠️ Gagal generate gambar, skip")
                    remaining.append(item)
                    continue

                print(f"   📤 Upload gambar...")
                image_url = cms.upload_image(image_path)
                print(f"   ✅ Image: {image_url}")

                print(f"   📝 Update article {article_id}...")
                cms.update_post(article_id, title=title, featured_image=image_url)
                print(f"   ✅ Artikel diupdate dengan gambar!")
                success += 1

            except Exception as e:
                print(f"   ⚠️ Gagal: {e}")
                remaining.append(item)

        self._save_image_queue(remaining)

        print(f"\n{'='*60}")
        print(f"✅ {success} gambar berhasil diproses")
        if remaining:
            print(f"⏳ {len(remaining)} masih antri (akan dicoba lagi nanti)")

        return success

    def generate_article(self, topic: str = "", image_prompt_override: str = "", published_at: str = "", used_titles: list[str] | None = None) -> dict:
        active_topic = topic or self.topic
        used_titles = used_titles or []
        print(f"\n{'='*60}")
        print(f"📝 Niche: {active_topic}")
        print(f"{'='*60}")

        # === SINGLE LLM CALL: judul + konten + tags + image sekaligus ===
        print("\n🪄  Generate artikel (1x request)...")
        used_str = ""
        if used_titles:
            used_str = "\n".join(f"- \"{t}\"" for t in used_titles[-5:])
        else:
            used_str = "Tidak ada"

        article_data = self.llm.generate_json(
            messages=[{"role": "user", "content": f"Buat artikel blog tentang: {active_topic}"}],
            system=self._fmt(FULL_ARTICLE_PROMPT).replace("{used_titles}", used_str),
            temperature=0.85,
        )

        title = article_data.get("title", "Untitled")
        content = article_data.get("content", "")
        excerpt = article_data.get("excerpt", "")
        category_name = article_data.get("category_name", "Umum")
        tags = article_data.get("tags", [])
        seo_title = article_data.get("seo_title", title)
        seo_description = article_data.get("seo_description", excerpt)
        seo_keywords = article_data.get("seo_keywords", "")
        image_prompt = image_prompt_override or article_data.get("image_prompt", "")

        print(f"   Judul: {title}")
        print(f"   Kategori: {category_name}")
        print(f"   Tags: {', '.join(tags[:5])}")
        print(f"   SEO: {seo_title[:50]}...")

        content = self._ensure_html_format(content)

        if not excerpt and content:
            import re
            stripped = re.sub(r'<[^>]+>', '', content).strip()
            excerpt = ' '.join(stripped.split()[:30]) + '...'

        # Retry jika konten terlalu pendek
        word_count = len(content.split())
        print(f"   📊 Panjang konten: ~{word_count} kata")
        if word_count < 300:
            print(f"   ⚠️ Konten terlalu pendek ({word_count} kata), minta LLM tulis ulang...")
            article_data = self.llm.generate_json(
                messages=[
                    {"role": "user", "content": f"Buat artikel blog tentang: {active_topic}"},
                    {"role": "assistant", "content": f"Title: {title}\n\n{content}"},
                    {"role": "user", "content": "Konten terlalu pendek! TULIS ULANG dengan minimal 800 kata. Kembangkan setiap bagian dengan contoh konkret, data, dan analisis mendalam."}
                ],
                system=self._fmt(FULL_ARTICLE_PROMPT).replace("{used_titles}", used_str),
                temperature=0.8,
            )
            content = article_data.get("content", content)
            excerpt = article_data.get("excerpt", excerpt)
            content = self._ensure_html_format(content)

        print(f"   ✅ Final: ~{len(content.split())} kata")

        # === PUBLISH ===
        print("\n📤 Publishing ke BlogCMS...")
        print(f"   📂 Kategori: {category_name}")
        categories = self.cms.get_categories()
        existing = [c for c in categories if c["name"].lower() == category_name.lower()]
        if existing:
            category_id = existing[0]["id"]
        else:
            cat = self.cms.create_category(category_name)
            category_id = cat["id"]

        pub_str = f" → {published_at}" if published_at else ""
        print(f"   📝 Publish artikel{pub_str}...")

        post_data = {
            "domain_url": self.cms.base_url,
            "title": title,
            "content": content,
            "excerpt": excerpt,
            "_category_name": category_name,
            "category_id": category_id,
            "status": "published",
            "tags": tags,
            "published_at": published_at,
        }

        try:
            post = self.cms.create_post(
                title=title,
                content=content,
                excerpt=excerpt,
                category_id=category_id,
                status="published",
                tags=tags,
                published_at=published_at,
                seo_title=seo_title,
                seo_description=seo_description,
                seo_keywords=seo_keywords,
            )
            print(f"\n✅ Artikel dipublikasikan!")
            print(f"   ID: {post['id']} - {post['title']}")
            print(f"   🔗 {self._domain_url}/blog/{post['slug']}")

            if image_prompt:
                self._enqueue_image(post, image_prompt)

            self._process_retry_queue()
            return post
        except Exception as e:
            print(f"   ⚠️ Gagal publish: {e}. Disimpan ke retry queue.")
            self._retry_queue.append(post_data)
            self._save_retry_queue()
            self._process_retry_queue()
            return {}

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

            for attempt in range(5):
                settings_data = self.llm.generate_json(
                    messages=[{
                        "role": "user",
                        "content": f"Buat identitas untuk website blog dengan domain {host} yang menggunakan tema '{dom['theme_slug']}'. "
                                   f"Buat site_name yang menarik, site_description (1-2 kalimat), dan site_topic (niche/kategori utama). "
                                   f"Kembalikan JSON: site_name, site_description, site_topic."
                    }],
                    temperature=0.8,
                )

                site_name = settings_data.get("site_name", "").strip()
                site_desc = settings_data.get("site_description", "").strip()
                site_topic = settings_data.get("site_topic", "").strip()

                if site_name and site_desc and site_topic:
                    break
                print(f"   ⚠️  Ada field kosong, regenerate percobaan {attempt+2}...")

            if not site_name:
                site_name = host.replace('.', ' ').title()
            if not site_desc:
                site_desc = f"Blog tentang {site_topic or host}"
            if not site_topic:
                site_topic = "Blog"

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

                try:
                    result = subprocess.run(
                        ["php", script_path, "--hostname", host, "--service", service_url],
                        capture_output=True, timeout=180,
                    )

                    out = result.stdout.decode('utf-8', errors='replace').strip() if result.stdout else ""
                    err = result.stderr.decode('utf-8', errors='replace').strip() if result.stderr else ""

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
                except subprocess.TimeoutExpired:
                    print(f"   [TIMEOUT] Cloudflare tunnel setup timed out for {host}")
                except Exception as e:
                    print(f"   [ERROR] {e}")

        print(f"\n{'='*60}")
        print("✅ Semua domain siap digunakan!")
        for d in created:
            print(f"   - {d['host']} (ID: {d['id']}, theme: {d['theme_slug']})")
        return created

    def generate_topics(self, count: int = 5) -> list[str]:
        print("⏳ Generate ide artikel...")
        try:
            result = self.llm.generate_json(
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
        used_titles = []
        for i in range(count):
            print(f"\n--- Artikel {i+1}/{count} ---")
            post = self.generate_article(topic=topic, used_titles=used_titles)
            if post.get("title"):
                used_titles.append(post["title"])
            results.append(post)
            if i < count - 1:
                print("   ⏳ Delay 30 detik biar tidak kena rate limit...")
                time.sleep(30)

        self._process_retry_queue()

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
            try:
                post = gen.generate_article(published_at=today)
                if post.get("id"):
                    total += 1
            except Exception as e:
                print(f"   ❌ Gagal: {e}")
            gen._process_retry_queue()

        print(f"\n✅ Selesai! {total} artikel dipublikasikan hari ini.")
        return total

    def run_schedule(self, days: int = 7, start_date: str | None = None):
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

            if start_date:
                start = datetime.strptime(start_date, "%Y-%m-%d")
            else:
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

            used_titles = []
            for i in range(need):
                pub_date = start + timedelta(days=i)
                pub_str = pub_date.strftime("%Y-%m-%d %H:%M:%S")
                print(f"\n--- Artikel {i+1}/{need} ({pub_date.strftime('%Y-%m-%d')}) ---")
                try:
                    post = gen.generate_article(published_at=pub_str, used_titles=used_titles)
                    if post.get("id"):
                        total += 1
                    if post.get("title"):
                        used_titles.append(post["title"])
                except Exception as e:
                    print(f"   ❌ Gagal generate artikel: {e}")
                    print(f"   ⏭️  Skip, lanjut ke artikel berikutnya...")
                if i < need - 1:
                    print("   ⏳ Delay 30 detik biar tidak kena rate limit...")
                    time.sleep(30)

            print()

        print(f'{"="*60}')
        print(f"✅ Selesai! Total {total} artikel terschedule:")
        for d in domains:
            print(f"   - {d['host']}")

        if self._retry_queue:
            print(f"\n⏳ Masih ada {len(self._retry_queue)} artikel dalam antrian retry.")

        return total
