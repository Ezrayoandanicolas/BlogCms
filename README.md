# BlogCMS

Multi-tenant Blog CMS dengan API-first, multi-theme, SEO engine, comment system + spam filter, backlink management, dan AI automation (Ollama + SD WebUI).

---

## Daftar Isi

- [Requirements](#requirements)
- [Installasi](#installasi)
- [Konfigurasi Nginx Multi-Port](#konfigurasi-nginx-multi-port)
- [Environment](#environment)
- [Command Tersedia](#command-tersedia)
  - [Artisan (Backend)](#artisan-backend)
  - [Automation Python](#automation-python)
- [Struktur Folder](#struktur-folder)
- [API Endpoints](#api-endpoints)
- [Multi-Tenant / Domain](#multi-tenant--domain)
- [Theme System](#theme-system)
- [Automation (AI Article Generation)](#automation-ai-article-generation)
- [Security Checklist](#security-checklist)

---

## Requirements

- **PHP** 8.1+
- **Composer**
- **MySQL** 8+
- **Redis**
- **Nginx**
- **Node.js** 18+ (untuk Vite/Tailwind)
- **Python** 3.9+ (untuk automation)
- **Ollama** (untuk AI generation, jalan di Windows)
- **Stable Diffusion WebUI** (untuk gambar, jalan di Windows)

---

## Installasi

### 1. Clone & Install Dependencies

```bash
cd /var/www/html/Project/BlogCms
git clone <repo-url> blogcms
cd blogcms

# PHP dependencies
composer install --no-interaction

# Node dependencies (Tailwind CSS)
npm install && npm run build

# Python dependencies (automation)
cd automation
python3 -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate
pip install requests pillow python-dotenv
```

### 2. Database Setup

```sql
CREATE DATABASE blogcms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Environment

```bash
cp .env.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, API_KEY, dll
```

**Wajib diubah:**
| Variable | Contoh | Keterangan |
|----------|--------|------------|
| `DB_DATABASE` | `blogcms` | Nama database |
| `DB_USERNAME` | `root` | User MySQL |
| `DB_PASSWORD` | `secret` | Password MySQL |
| `API_KEY` | `your-strong-secret-key` | API key untuk domain management (ganti!) |
| `APP_URL` | `http://localhost:8101` | URL utama |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:8101,localhost:8102,localhost:8103` | Domain yang boleh SPA auth |

### 4. Migrate & Seed

```bash
php artisan key:generate
php artisan migrate --seed
```

Seeder membuat:
- **Admin**: `admin@blogcms.test` / `password`
- **3 domain**: localhost:8101 (theme: default), localhost:8102 (theme: magazine), localhost:8103 (theme: dark)
- **7 themes**: default, blue, bold, clean, dark, magazine, compact
- **Kategori + settings default**

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Nginx

```nginx
# /etc/nginx/sites-enabled/8101-blogcms.conf
server {
    listen 0.0.0.0:8101;
    root /var/www/html/Project/BlogCms/blogcms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~* \.(?:ico|css|js|gif|jpe?g|png|woff2?|eot|ttf|svg|webp)$ {
        expires 6M;
        add_header Cache-Control "public, immutable";
    }
}
```

Ulangi untuk port 8102 dan 8103 (copy-paste, ganti port).

```bash
sudo nginx -t && sudo systemctl reload nginx
```

### 7. Final Check

```bash
# Akses di browser
http://localhost:8101           # Frontend
http://localhost:8101/admin     # Admin panel (login: admin@blogcms.test / password)
http://localhost:8102           # Domain kedua (theme: magazine)
http://localhost:8103           # Domain ketiga (theme: dark)

# API check
curl http://localhost:8101/api/v1/settings
```

---

## Konfigurasi Nginx Multi-Port

Setiap domain = 1 port Nginx → 1 `domain_themes` record.

| Port | Theme | Keterangan |
|------|-------|------------|
| 8101 | default | Admin utama + frontend |
| 8102 | magazine | Domain isolasi content |
| 8103 | dark | Domain isolasi content |
| 8104+ | random | Tambah via API / automation |

Isolasi konten per domain dijamin oleh `TenantScope` + `domain_id` column.

**Tambah port baru:**
```bash
cp /etc/nginx/sites-enabled/8101-blogcms.conf /etc/nginx/sites-enabled/8104-blogcms.conf
# Ganti 8101 → 8104 di dalam file
sudo nginx -t && sudo systemctl reload nginx
```

---

## Environment

### BlogCMS `.env`

```
APP_NAME=BlogCMS
APP_ENV=local
APP_DEBUG=true          # → false untuk production!
APP_URL=http://localhost:8101
API_KEY=blogcms-api-key-rahasia   # GANTI dengan key kuat!

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blogcms
DB_USERNAME=root
DB_PASSWORD=secret

CACHE_DRIVER=redis
SESSION_DRIVER=redis

BACKLINK_API_URL=https://device.quailtv.org/api
```

### Automation `.env` (`automation/.env`)

```
BLOGCMS_URL=http://localhost:8101
BLOGCMS_EMAIL=admin@blogcms.test
BLOGCMS_PASSWORD=password
BLOGCMS_API_KEY=blogcms-api-key-rahasia

OLLAMA_URL=http://localhost:11434/api/chat
OLLAMA_MODEL=llama3.2

SD_WEBUI_URL=http://127.0.0.1:7860
SD_MODEL=mdjrny-v4.safetensors
SD_STEPS=20
SD_WIDTH=768
SD_HEIGHT=512
```

---

## Command Tersedia

### Artisan (Backend)

| Command | Fungsi |
|---------|--------|
| `php artisan serve --port=8101` | Dev server (alternatif Nginx) |
| `php artisan migrate` | Jalankan migrasi |
| `php artisan migrate:fresh --seed` | Reset DB + seed ulang |
| `php artisan db:seed` | Seed ulang (tanpa reset) |
| `php artisan storage:link` | Link storage publik |
| `php artisan cache:clear` | Clear cache |
| `php artisan config:clear` | Clear config cache |
| `php artisan route:list` | Lihat semua route |
| `php artisan test` | Jalankan PHPUnit tests |
| `php artisan tinker` | Interactive shell |

### Automation Python

Semua command jalan dari folder `automation/`. **Wajib** Ollama + SD WebUI jalan (di Windows).

```bash
cd /var/www/html/Project/BlogCms/blogcms/automation
source venv/bin/activate  # Windows: venv\Scripts\activate
```

| Command | Fungsi |
|---------|--------|
| `python run.py` | Generate 1 artikel untuk domain pertama |
| `python run.py --count 5` | Batch 5 artikel |
| `python run.py --topic "Tips Kesehatan"` | Override topik |
| `python run.py --daily` | 1 artikel per domain hari ini |
| `python run.py --schedule 7` | 7 artikel per domain (terjadwal) |
| `python run.py --topics` | Tampilkan ide artikel (5 rekomendasi) |
| `python run.py --image-prompt "forest fog"` | Override prompt gambar |
| `python run.py --add-domain localhost:8104` | Tambah domain baru (Ollama generate settings) |
| `python run.py --add-domain localhost:8104 localhost:8105` | Tambah multiple domain sekaligus |

**Contoh automation + domain:**
```bash
# 1. Tambah 2 domain baru
python run.py --add-domain localhost:8104 localhost:8105

# 2. Isi konten untuk semua domain (termasuk baru)
python run.py --schedule 7
```

---

## Struktur Folder

```
blogcms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/AdminController.php    # Admin panel CRUD
│   │   │   ├── Api/V1/                      # API Controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── PostController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── TagController.php
│   │   │   │   ├── CommentController.php
│   │   │   │   ├── DomainController.php     # Domain CRUD
│   │   │   │   ├── SettingController.php    # Settings get/update
│   │   │   │   └── ...
│   │   │   └── Web/                         # Frontend controllers
│   │   ├── Middleware/
│   │   │   ├── DomainThemeMiddleware.php     # Domain → theme mapping
│   │   │   ├── SetAdminDomain.php           # Admin domain switcher
│   │   │   ├── ApiKeyMiddleware.php         # API Key protection
│   │   │   └── AdminMiddleware.php
│   ├── Models/
│   │   ├── Post.php, Category.php, Tag.php, ...
│   │   ├── DomainTheme.php                  # Domain mapping model
│   │   ├── Setting.php                      # Per-domain settings
│   │   ├── Theme.php                        # Theme model
│   │   ├── Media.php                        # Media dengan WebP
│   │   ├── Comment.php                       # Comment + spam filter
│   │   └── Scopes/TenantScope.php           # Global domain filter
│   ├── Services/                            # Business logic layer
│   ├── Repositories/                        # Data layer
│   └── helpers.php                          # Helper functions
├── automation/                              # Python AI scripts
│   ├── run.py                               # CLI entry point
│   ├── generator.py                         # Article pipeline
│   ├── api_client.py                        # BlogCMS API wrapper
│   ├── ollama_client.py                     # Ollama API client
│   ├── image_generator.py                   # SD WebUI client
│   ├── prompt_templates.py                  # AI prompt templates
│   └── config.py                            # Config dari .env
├── config/
│   ├── app.php                              # App config + api_key
│   ├── cors.php                             # CORS settings
│   ├── sanctum.php                          # Sanctum auth config
│   └── filesystems.php                      # File storage config
├── database/
│   └── migrations/                          # 24+ migration files
├── resources/
│   ├── themes/                              # 7 theme folders
│   │   ├── default/                         # Blue theme (sidebar right)
│   │   ├── blue/                            # Cyan theme (sidebar left)
│   │   ├── bold/                            # Orange/editorial (no sidebar)
│   │   ├── clean/                           # Teal/minimalist (sidebar right)
│   │   ├── dark/                            # Purple/dark mode (sidebar right)
│   │   ├── magazine/                        # Red/newspaper (sidebar both)
│   │   └── compact/                         # Sky/dense (sidebar right)
│   └── css/app.css                         # Tailwind + article styles
├── routes/
│   ├── api.php                              # 50+ API endpoints
│   └── web.php                              # Frontend + admin routes
├── public/
│   └── .htaccess                            # → TAMBAHKAN aturan blok .env!
└── DOMAIN_API.md                            # Dokumentasi API domain
```

---

## API Endpoints

### Public (tanpa auth)

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/v1/auth/register` | Register user |
| POST | `/api/v1/auth/login` | Login → dapat token |
| GET | `/api/v1/posts` | Daftar published posts |
| GET | `/api/v1/posts/popular` | Post terpopuler |
| GET | `/api/v1/posts/search?q=` | Cari post |
| GET | `/api/v1/posts/{slug}` | Detail post by slug |
| GET | `/api/v1/categories` | Daftar kategori |
| GET | `/api/v1/tags` | Daftar tags |
| GET | `/api/v1/pages` | Daftar halaman |
| GET | `/api/v1/themes` | Daftar theme aktif |
| GET | `/api/v1/settings` | Site settings (name, desc, topic) |
| GET | `/api/v1/domains` | Daftar domain aktif |
| GET | `/api/v1/og-image?title=` | Generate OG image |

### Authenticated (Sanctum)

Semua endpoint di bawah public +:

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/v1/posts` | Buat post |
| PUT | `/api/v1/posts/{id}` | Update post |
| DELETE | `/api/v1/posts/{id}` | Hapus post |
| POST | `/api/v1/categories` | Buat kategori |
| POST | `/api/v1/media/upload` | Upload gambar (auto WebP) |
| GET | `/api/v1/comments` | Daftar comments (admin) |
| PUT | `/api/v1/comments/{id}/approve` | Approve comment |
| PUT | `/api/v1/comments/{id}/reject` | Reject comment |
| POST | `/api/v1/backlinks` | Buat backlink |
| ... | ... | + 30+ endpoint lainnya |

### API Key Protected

| Method | Endpoint | Fungsi |
|--------|----------|--------|
| POST | `/api/v1/domains` | Tambah domain baru |
| PUT | `/api/v1/domains/{id}` | Edit domain |
| DELETE | `/api/v1/domains/{id}` | Hapus domain |
| PUT | `/api/v1/settings` | Update settings per domain |

Header: `X-API-Key: your-api-key`

---

## Multi-Tenant / Domain

### Cara Kerja

1. Setiap request masuk → `DomainThemeMiddleware` deteksi `host:port` dari URL
2. Cocokkan ke tabel `domain_themes` → ambil `theme_slug` + `domain_id`
3. `TenantScope` otomatis filter semua query berdasarkan `config('app.domain_id')`
4. Admin bisa switch domain via `/admin/switch-domain/{id}`

### Admin Panel

```
Admin Login:  http://localhost:8101/admin
Email:        admin@blogcms.test
Password:     password
```

Fitur admin:
- **Dashboard**: Grafik statistik, per-domain breakdown, posts this week
- **Posts**: CRUD, calendar view, bulk actions, scheduling
- **Comments**: Moderation (approve/reject), spam filter
- **Categories & Tags**: Manage taxonomy
- **Media Library**: Upload, WebP auto-convert, copy URL
- **Settings**: Site name, description, topic per domain
- **Domain Switcher**: Ganti domain context dari sidebar
- **Themes**: Skip/aktifkan theme

### Tambah Domain Baru

**Via Admin Panel:**
- Buka `/admin/settings` → isi form domain + theme

**Via API:**
```bash
curl -X POST http://localhost:8101/api/v1/domains \
  -H "X-API-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"domains":["localhost:8104"]}'
```
Theme dipilih random dari yang aktif.

**Via Automation (otomatis + Ollama generate settings):**
```bash
cd automation
python run.py --add-domain localhost:8104
```

---

## Theme System

7 theme dengan layout unik:

| Theme | Warna | Sidebar | Fitur Khas |
|-------|-------|---------|------------|
| default | Blue | Right | Standard blog |
| blue | Cyan | Left | Sidebar kiri |
| bold | Orange | None | Editorial, full-width |
| clean | Teal | Right | Minimalist, clean |
| dark | Purple | Right | Dark mode |
| magazine | Red | Both | Newspaper layout, grid |
| compact | Sky | Right | Dense, compact cards |

Theme aktif per domain disimpan di `domain_themes.theme_slug`.

---

## Automation (AI Article Generation)

### Arsitektur

```
├── run.py               # CLI entry point
├── generator.py         # Pipeline: outline → content → tags → image → publish
├── api_client.py        # HTTP client ke BlogCMS API
├── ollama_client.py     # Client ke Ollama (generate teks)
├── image_generator.py   # Client ke SD WebUI (generate gambar)
├── prompt_templates.py  # Prompt templates untuk LLM
└── config.py            # Konfigurasi dari .env
```

### Pipeline Generate Artikel

```
User input (topic)
    │
    ▼
[1] Ollama → Outline (JSON: title, category, outline[])
    │
    ▼
[2] Ollama → Content (HTML: 800+ words, h2/p/ul)
    │  (auto-regenerate if <300 words)
    ▼
[3] Ollama → Tags (3-5 tags)
    │
    ▼
[4] Ollama → Image Prompt → SD WebUI → PNG
    │
    ▼
[5] BlogCMS API → Create category, upload image, create post
```

### Persiapan Windows (untuk automation)

```powershell
# 1. Buka Ollama (model llama3.2 atau lainnya)
ollama pull llama3.2
ollama serve

# 2. Buka Stable Diffusion WebUI dengan API
cd stable-diffusion-webui
python webui.py --api --listen

# 3. Setup Python
cd automation
python -m venv venv
venv\Scripts\activate
pip install requests pillow python-dotenv

# 4. Edit .env
notepad .env
# BLOGCMS_URL=http://<ip-server>:8101
# OLLAMA_URL=http://localhost:11434/api/chat
# SD_WEBUI_URL=http://127.0.0.1:7860

# 5. Generate
python run.py --daily
python run.py --schedule 7
```

---

## Security Checklist

### Sebelum Production

| Priority | Issue | Fix |
|----------|-------|-----|
| 🔴 CRITICAL | `APP_DEBUG=true` | Set `APP_DEBUG=false` di `.env` |
| 🔴 CRITICAL | `.env` bisa diakses publik | Tambah di `public/.htaccess`: |
| 🔴 CRITICAL | API key lemah | Ganti `API_KEY` di `.env` + `automation/.env` |
| 🟠 HIGH | CORS wildcard `*` | Edit `config/cors.php` → batasi origins |
| 🟠 HIGH | `SESSION_SECURE_COOKIE` | Set `SESSION_SECURE_COOKIE=true` (via HTTPS) |
| 🟠 HIGH | Post content XSS | Tambah HTML sanitizer (HTMLPurifier) |
| 🟠 HIGH | Comment auto-approved | Pastikan spam filter aktif |
| 🟡 MEDIUM | Redis tanpa password | Set `REDIS_PASSWORD` |
| 🟡 MEDIUM | Rate limiting | Sesuaikan `ThrottleRequests` |

### `.htaccess` untuk blok `.env`

```apache
# Tambahkan di public/.htaccess
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

---

## Catatan Penting

1. **Domain ≠ hostname**: Multi-tenant via `domain_id` column, **bukan** subdomain terpisah
2. **Gambar**: Auto-convert ke WebP via GD saat upload
3. **OG Image**: Generate otomatis 1200x630 PNG via GD
4. **Sitemap**: Dinamis per-domain, filter by `domain_id`
5. **Schedule**: `published_at` mendukung future date (post disembunyikan sampai tanggal tiba)
6. **Automation**: Jalan di Windows (karena butuh Ollama + SD WebUI lokal), bukan di server
7. **Admin panel** selalu pakai theme `default`, tidak terpengaruh theme domain
