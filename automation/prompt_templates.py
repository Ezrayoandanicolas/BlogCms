FULL_ARTICLE_PROMPT = """Kamu adalah penulis artikel blog profesional untuk website niche **{topic}**.

Buat SATU artikel blog lengkap dalam bahasa Indonesia berdasarkan topik yang diberikan.

Kembalikan JSON SAJA (tanpa markdown, tanpa penjelasan):
{
  "title": "Judul click-worthy dan unik tentang {topic}",
  "content": "<h2>Sub judul 1</h2><p>Paragraf detail...</p><h2>Sub judul 2</h2><p>Paragraf detail...</p>",
  "excerpt": "Ringkasan 2-3 kalimat yang informatif",
  "category_name": "nama kategori relevan (max 3 kata)",
  "tags": ["tag1", "tag2", "tag3"],
  "seo_title": "SEO title optimasi (60-70 karakter)",
  "seo_description": "Meta description 150-160 karakter",
  "seo_keywords": "kata kunci 1, kata kunci 2, kata kunci 3",
  "image_prompt": "Prompt gambar realistis bahasa Inggris untuk Stable Diffusion (max 60 kata)"
}

ATURAN KONTEN:
- Konten minimal 500 kata
- Struktur HTML rapi: <h2> untuk sub judul, <p> untuk paragraf
- Setiap <p> berisi 2-4 kalimat, jangan gabung paragraf
- Minimal 4 sub judul (<h2>), masing-masing minimal 2 paragraf
- Gunakan <ul>/<ol> untuk list poin penting
- JANGAN gunakan <br> untuk paragraf
- Bahasa Indonesia natural, informatif, enak dibaca
- Berikan contoh konkret dan data pendukung

ATURAN JUDUL:
- Judul UNIK, jangan pakai judul generik atau itu-itu saja
- Gunakan angle/perspektif yang fresh
- Jangan copy judul dari artikel lain

ATURAN IMAGE PROMPT:
- Prompt REALISTIS/FOTOGRAFIS untuk Stable Diffusion (Realistic Vision)
- Format: quality tags + subjek + latar + pencahayaan + gaya + warna
- Jangan pakai: fantasy, surreal, illustration, painting, drawing
- Contoh: "masterpiece, best quality, photorealistic, (highly detailed:1.1), a scenic view of..., natural lighting, cinematic composition"

USED_TITLES (jika ada, JANGAN buat judul yang mirip):
{used_titles}

Kategori dan tags harus relevan dengan niche: {topic}"""


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