# 🚀 Panduan Instalasi — AntriYuk

## Prasyarat

Pastikan sistem kamu sudah memiliki:

| Dependensi | Versi Minimum | Perintah Cek |
|-----------|--------------|-------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 8+ | `npm -v` |
| SQLite | — | `sqlite3 --version` |
| Git | — | `git --version` |

> **Opsional:** MySQL/MariaDB jika ingin menggunakan database non-SQLite.

---

## Opsi A: Setup Cepat (Direkomendasikan)

```bash
# 1. Clone repository
git clone https://github.com/your-username/antriyuk.git
cd antriyuk

# 2. Setup otomatis (install PHP deps, salin .env, generate key, migrate, build assets)
composer setup

# 3. Isi data contoh
php artisan db:seed

# 4. Jalankan semua server development
composer dev

# Di terminal terpisah — jalankan WebSocket server
php artisan reverb:start
```

Buka browser di: **http://localhost:8000**

---

## Opsi B: Setup Manual (Step-by-Step)

### Langkah 1 — Clone & Install Dependensi PHP

```bash
git clone https://github.com/your-username/antriyuk.git
cd antriyuk

composer install
```

### Langkah 2 — Konfigurasi Environment

```bash
# Salin file .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Langkah 3 — Setup Database

**Untuk SQLite (default):**
```bash
touch database/database.sqlite
```

**Untuk MySQL:**
Edit `.env`:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=antriyuk
DB_USERNAME=root
DB_PASSWORD=your_password
```

Lalu buat database di MySQL:
```sql
CREATE DATABASE antriyuk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah 4 — Jalankan Migrasi & Seeder

```bash
# Jalankan migrasi
php artisan migrate

# Isi data contoh (akun demo, lokasi, counter)
php artisan db:seed
```

### Langkah 5 — Install Dependensi Node.js & Build Assets

```bash
npm install
npm run build
```

### Langkah 6 — Konfigurasi WebSocket (Reverb)

Edit `.env` dan tambahkan/sesuaikan:

```dotenv
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=antriyuk-app
REVERB_APP_KEY=antriyuk-key-local
REVERB_APP_SECRET=antriyuk-secret-local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Rebuild assets setelah mengubah `.env` Vite:
```bash
npm run build
```

### Langkah 7 — Jalankan Server

```bash
# Terminal 1: Server Laravel + Vite (concurrent)
composer dev

# Terminal 2: WebSocket server
php artisan reverb:start
```

---

## Perintah Development (composer dev)

`composer dev` menjalankan tiga proses secara bersamaan menggunakan `concurrently`:

| Proses | Perintah | Deskripsi |
|--------|---------|---------|
| `server` | `php artisan serve` | Laravel web server (port 8000) |
| `queue` | `php artisan queue:listen --tries=1` | Queue worker untuk broadcast events |
| `vite` | `npm run dev` | Vite dev server dengan HMR |

---

## Opsi C: Deployment dengan Docker

### Prasyarat Docker
- Docker Engine 24+
- Docker Compose v2

### Setup Docker

```bash
# 1. Clone repository
git clone https://github.com/your-username/antriyuk.git
cd antriyuk

# 2. Buat file .env
cp .env.example .env

# Edit .env untuk production
```

Konfigurasi `.env` minimal untuk Docker:
```dotenv
APP_NAME=AntriYuk
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_KEY
APP_DEBUG=false
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=antriyuk-prod
REVERB_APP_KEY=your-production-key
REVERB_APP_SECRET=your-production-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=your-production-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### Jalankan Docker

```bash
# Build & jalankan container
docker compose up -d

# Jalankan migrasi & seeder (pertama kali)
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed

# Cek status container
docker compose ps

# Lihat logs
docker compose logs -f app
```

Aplikasi berjalan di: **http://localhost:8080**

### Struktur Docker

| Komponen | Detail |
|---------|--------|
| Image Base | `php:8.4-fpm-alpine` |
| Web Server | Nginx |
| Process Manager | Supervisor |
| Port Exposed | 80 (HTTP) |
| Volumes | `app_storage`, `app_database` |

Supervisor mengelola proses:
- `php-fpm` — PHP runtime
- `nginx` — web server
- `reverb` — WebSocket server
- `queue:listen` — queue worker

---

## Troubleshooting

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Error: "Database file not found" (SQLite)
```bash
touch database/database.sqlite
php artisan migrate
```

### WebSocket tidak terhubung
1. Pastikan `php artisan reverb:start` sedang berjalan
2. Periksa `REVERB_APP_KEY` di `.env` sama dengan `VITE_REVERB_APP_KEY`
3. Pastikan port 8080 tidak diblokir firewall

### Error: "Broadcast driver [reverb] is not defined"
Pastikan `BROADCAST_CONNECTION=reverb` ada di `.env`, bukan `BROADCAST_CONNECTION=log`.

### Asset tidak ter-load (CSS/JS 404)
```bash
# Development
npm run dev

# Production
npm run build
```

### Queue events tidak terkirim
Pastikan queue worker berjalan:
```bash
php artisan queue:listen --tries=1
```

---

## Perintah Artisan Berguna

```bash
# Reset database + re-seed
php artisan migrate:fresh --seed

# Clear semua cache
php artisan optimize:clear

# Lihat semua route
php artisan route:list

# Buat user admin secara manual via tinker
php artisan tinker
>>> App\Models\User::create(['name'=>'Admin', 'email'=>'admin@test.com', 'password'=>bcrypt('password'), 'role'=>'admin'])
```
