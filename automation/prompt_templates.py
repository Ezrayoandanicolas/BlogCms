OUTLINE_PROMPT = """Kamu adalah penulis blog profesional untuk website niche **{topic}**.

Buat outline artikel blog dalam bahasa Indonesia yang sesuai dengan niche website.

Berdasarkan topik yang diberikan, buat judul yang menarik dan outline konten yang terstruktur.

Kembalikan JSON:
{
  "title": "Judul artikel click-worthy tentang {topic}",
  "category": "nama kategori relevan (max 2 kata)",
  "outline": [
    {"subtitle": "Sub judul 1", "points": ["poin 1", "poin 2", "poin 3"]},
    {"subtitle": "Sub judul 2", "points": ["poin 1", "poin 2", "poin 3"]}
  ]
}

Aturan:
- Judul UNIK, beda dari judul blog lain pada niche yang sama
- Jangan pakai judul yang generik atau itu-itu saja
- Gunakan angle/perspektif yang fresh dan berbeda
- 4-6 subtitle dalam outline
- Category relevan dengan niche website
- Bahasa Indonesia"""


CONTENT_PROMPT = """Kamu adalah penulis artikel blog profesional untuk website niche **{topic}**.

Buat konten artikel yang detail, panjang, dan informatif berdasarkan judul dan outline berikut.

WAJIB: Struktur HTML yang benar dan rapi. Contoh format yang HARUS diikuti:
- <h2>Sub judul</h2> untuk setiap bagian
- <p>Isi paragraf di sini. Kalimat pertama... Kalimat kedua...</p> untuk setiap paragraf
- <ul><li>item 1</li><li>item 2</li></ul> atau <ol><li>item 1</li><li>item 2</li></ol> untuk list
- JANGAN gunakan <br> untuk paragraf — gunakan <p>...</p>
- JANGAN gabung multiple paragraf dalam satu <p> — pisah per ide
- Setiap <p> berisi 2-4 kalimat, jangan lebih

Kembalikan JSON:
{
  "content": "<h2>Sub judul 1</h2><p>Paragraf pertama tentang sub judul ini. Kembangkan ide dengan detail dan contoh konkret.</p><p>Paragraf kedua dengan informasi tambahan, data pendukung, dan analisis lebih lanjut.</p><h2>Sub judul 2</h2><p>Paragraf pertama untuk sub judul kedua. Jelaskan poin-poin penting dengan bahasa yang enak dibaca.</p><ul><li>Poin penting pertama dengan penjelasan</li><li>Poin penting kedua dengan detail</li><li>Poin penting ketiga sebagai kesimpulan</li></ul><p>Paragraf penutup yang merangkum semua informasi di atas.</p>",
  "excerpt": "Ringkasan 2-3 kalimat yang menggambarkan isi artikel"
}

Aturan:
- Konten minimal 800 kata (3000+ karakter)
- Setiap sub judul minimal 2 paragraf
- Gunakan <h2> untuk sub judul, <p> untuk paragraf, <ul>/<ol> untuk list
- TULIS PANJANG DAN DETAIL — jangan pendek
- Berikan contoh konkret, data, atau studi kasus di setiap bagian
- Bahasa Indonesia yang natural, informatif, dan enak dibaca
- Kembangkan setiap poin outline menjadi paragraf yang berbobot
- Jangan keluar dari judul dan outline yang diberikan
- Semua konten harus relevan dengan niche website: {topic}"""


TAGS_PROMPT = """Berdasarkan judul dan konten artikel berikut, tentukan tags yang relevan untuk website niche **{topic}**.

Kembalikan JSON:
{
  "tags": ["tag1", "tag2", "tag3"]
}

Aturan:
- 3-5 tags
- Gunakan bahasa Inggris
- Relevan dengan topik artikel dan niche {topic}
- Format lowercase"""


IMAGE_PROMPT_SYSTEM = """Buatkan prompt gambar REALISTIS/FOTOGRAFIS dalam bahasa Inggris untuk Stable Diffusion (model Realistic Vision) berdasarkan judul artikel blog berikut.

Website niche: **{topic}**

Kembalikan HANYA prompt text saja, tanpa markdown, tanpa kutipan, tanpa label, tanpa intro.

STRUKTUR PROMPT WAJIB:
1. **Quality tags** di awal: masterpiece, best quality, ultra high res, photorealistic, 8k
2. **Subjek:** deskripsi subjek utama (orang/benda/pemandangan) dengan detail
3. **Lingkungan/Latar:** deskripsi background dan setting
4. **Pencahayaan:** natural lighting, soft lighting, dramatic lighting, golden hour, studio light, dll
5. **Gaya foto:** photography style (cinematic, macro, wide angle, portrait, landscape, dll)
6. **Warna:** color palette yang sesuai (vibrant, pastel, monochrome, dll)

Aturan:
- Prompt harus REALISTIS, FOTOGRAFIS — cocok untuk model foto realistis
- Jangan pake kata "fantasy, surreal, illustration, painting, drawing, anime, cartoon"
- Fokus pada visual yang relevan dengan judul dan niche {topic}
- Maksimal 80 kata
- Gunakan keyword untuk Realistic Vision: (photorealistic:1.2), (highly detailed:1.1)

Contoh prompt bagus:
"masterpiece, best quality, ultra high res, photorealistic, (highly detailed:1.1), a professional young woman working at a modern office desk, smiling confidently, wearing business casual attire, soft natural window lighting from the left, shallow depth of field, cinematic composition, warm color tones"
"""

TOPIC_EXTRACTION_PROMPT = """Buatkan 5 ide judul artikel untuk website niche **{topic}**.

Kembalikan HANYA array JSON, tanpa properti atau objek lain:
["Judul artikel 1", "Judul artikel 2", "Judul artikel 3", "Judul artikel 4", "Judul artikel 5"]"""
