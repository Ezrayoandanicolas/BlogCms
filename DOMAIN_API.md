# Domain Management API + Automation

## API Key

Semua endpoint **Domain Management** dan **Update Settings** dilindungi oleh API Key.

**Cara setting:**
1. Di `.env` BlogCMS: `API_KEY=blogcms-api-key-rahasia`
2. Di `automation/.env`: `BLOGCMS_API_KEY=blogcms-api-key-rahasia`
3. Semua request API wajib menyertakan header: `X-API-Key: blogcms-api-key-rahasia`

> **Catatan:** Ubah `blogcms-api-key-rahasia` dengan password yang lebih kuat!

---

## Endpoint API (Protected by API Key)

### 1. Create Domains
```http
POST /api/v1/domains
X-API-Key: your-api-key
Content-Type: application/json

{
    "domains": ["localhost:8104", "localhost:8105"]
}
```

**Response (201):**
```json
{
    "message": "Domains created successfully",
    "data": [
        {
            "id": 4,
            "host": "localhost:8104",
            "theme_slug": "magazine",
            "active": true
        },
        {
            "id": 5,
            "host": "localhost:8105",
            "theme_slug": "dark",
            "active": true
        }
    ]
}
```

> Theme slug dipilih **random** dari daftar theme yang aktif di server.

---

### 2. Update Domain
```http
PUT /api/v1/domains/{id}
X-API-Key: your-api-key
Content-Type: application/json

{
    "domain": "localhost:8104",
    "theme_slug": "default",
    "active": true
}
```

**Response (200):**
```json
{
    "message": "Domain updated successfully",
    "data": {
        "id": 4,
        "host": "localhost:8104",
        "theme_slug": "default",
        "active": true
    }
}
```

---

### 3. Delete Domain
```http
DELETE /api/v1/domains/{id}
X-API-Key: your-api-key
```

**Response (200):**
```json
{
    "message": "Domain deleted successfully"
}
```

---

### 4. Update Settings (Domain Name, Description, Topic)
```http
PUT /api/v1/settings
X-API-Key: your-api-key
Content-Type: application/json

{
    "domain_id": 4,
    "settings": {
        "site_name": "Blog Teknologi Modern",
        "site_description": "Artikel terbaru seputar teknologi dan programming",
        "site_topic": "Teknologi, Programming, AI"
    }
}
```

**Response (200):**
```json
{
    "message": "Settings updated successfully",
    "data": {
        "domain_id": 4,
        "site_name": "Blog Teknologi Modern",
        "site_description": "Artikel terbaru seputar teknologi dan programming",
        "site_topic": "Teknologi, Programming, AI"
    }
}
```

---

## Automation (Python)

### Tambah Domain Baru dengan AI

```bash
cd automation
python run.py --add-domain localhost:8104 localhost:8105
```

**Apa yang terjadi:**
1. Fetch daftar theme dari server
2. Kirim `POST /api/v1/domains` → server buat domain dengan theme random
3. Untuk setiap domain yang berhasil, Ollama generate:
   - `site_name` (nama blog yang menarik)
   - `site_description` (deskripsi 1-2 kalimat)
   - `site_topic` (niche/kategori utama)
4. Kirim `PUT /api/v1/settings` untuk menyimpan hasil generate

**Contoh output:**
```
🏗️  Menambahkan 2 domain baru...

✅ 2 domain berhasil dibuat!

🏠 localhost:8104
   Theme: magazine
   ID   : 4
   🤖 Generate site_name, description, topic via Ollama...
   Name      : Revolusi Teknologi
   Desc      : Menjelajahi inovasi teknologi terkini, tips programming...
   Topic     : Teknologi, Programming, Inovasi Digital
   💾 Simpan settings ke BlogCMS...
   ✅ Settings tersimpan

🏠 localhost:8105
   Theme: dark
   ID   : 5
   🤖 Generate site_name, description, topic via Ollama...
   Name      : Dunia AI & Data
   Desc      : Panduan lengkap artificial intelligence, machine learning...
   Topic     : Artificial Intelligence, Data Science
   💾 Simpan settings ke BlogCMS...
   ✅ Settings tersimpan
```

---

## Automation (.env)

```
BLOGCMS_URL=http://localhost:8101
BLOGCMS_EMAIL=admin@blogcms.test
BLOGCMS_PASSWORD=password
BLOGCMS_TOKEN=
BLOGCMS_API_KEY=blogcms-api-key-rahasia
```

---

## Alur Lengkap

```
1. User/Automation
   │
   ├─► POST /api/v1/domains (X-API-Key)
   │     ├─► Server cek API Key
   │     ├─► Ambil random theme dari themes aktif
   │     └─► Buat domain_themes record
   │
   ├─► [Automation] Ollama generate site_name, desc, topic
   │
   └─► PUT /api/v1/settings (X-API-Key)
         ├─► Server cek API Key
         ├─► updateOrCreate settings by key + domain_id
         └─► Domain siap dipakai
```
