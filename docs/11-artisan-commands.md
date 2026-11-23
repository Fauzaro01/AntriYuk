# ⚙️ Artisan Commands & Utilitas — AntriYuk

Referensi semua Artisan command yang relevan untuk pengembangan, maintenance, dan operasional harian AntriYuk.

---

## Custom Commands

### `queue:reset-daily`

**File:** `app/Console/Commands/ResetDailyQueue.php`

Membatalkan semua tiket yang masih berstatus `waiting` atau `calling` dari **hari sebelumnya** secara otomatis. Berguna untuk memastikan antrian selalu bersih di awal hari kerja baru.

```bash
php artisan queue:reset-daily
```

**Apa yang dilakukan:**
- Mencari tiket dengan status `waiting` atau `calling` yang dibuat **sebelum hari ini**
- Mengubah statusnya menjadi `cancelled`
- Mengisi `completed_at` dengan timestamp sekarang
- Mengisi `notes` dengan pesan: _"Auto-cancelled: antrian direset otomatis di awal hari baru."_

**Output contoh:**
```
Reset antrian selesai. 12 tiket dibatalkan otomatis.
```

**Rekomendasi jadwal (cron):** Jalankan setiap hari pukul 00:05 pagi.

```bash
# Contoh di crontab
5 0 * * * cd /var/www/html && php artisan queue:reset-daily >> /dev/null 2>&1

# Atau menggunakan Laravel Task Scheduling di app/Console/Kernel.php (jika ada):
$schedule->command('queue:reset-daily')->dailyAt('00:05');
```

---

## Commands Laravel Standar yang Sering Digunakan

### 📦 Setup & Instalasi

```bash
# Setup lengkap (install deps, .env, key, migrate, build assets)
composer setup

# Install dependensi PHP
composer install

# Generate application key
php artisan key:generate

# Jalankan semua migrasi
php artisan migrate

# Jalankan migrasi + reset database (WARNING: menghapus semua data!)
php artisan migrate:fresh

# Jalankan migrasi + seed data contoh
php artisan migrate:fresh --seed

# Seed data tanpa reset migrasi
php artisan db:seed
```

---

### 🚀 Menjalankan Server

```bash
# Development: server + queue + vite (concurrent)
composer dev

# Hanya Laravel server
php artisan serve

# Laravel server di port tertentu
php artisan serve --port=8080

# WebSocket server (Reverb)
php artisan reverb:start

# WebSocket server dengan mode debug
php artisan reverb:start --debug

# WebSocket server di host & port tertentu
php artisan reverb:start --host=0.0.0.0 --port=8080

# Queue worker
php artisan queue:listen --tries=1
php artisan queue:work --sleep=3 --tries=3
```

---

### 🗄️ Database

```bash
# Lihat status migrasi
php artisan migrate:status

# Rollback migrasi terakhir
php artisan migrate:rollback

# Rollback N langkah
php artisan migrate:rollback --step=3

# Buat migrasi baru
php artisan make:migration create_table_name_table
php artisan make:migration add_column_to_table_name_table

# Reset seluruh database (rollback semua migrasi)
php artisan migrate:reset

# Refresh (reset + migrate kembali)
php artisan migrate:refresh --seed
```

---

### 🔧 Membuat File (make:*)

```bash
# Model (dengan factory -f, migration -m, controller -c sekaligus)
php artisan make:model ModelName -f -m -c

# Model terpisah
php artisan make:model ModelName
php artisan make:factory ModelNameFactory --model=ModelName
php artisan make:migration create_model_names_table
php artisan make:controller ModelNameController

# Event
php artisan make:event EventName

# Middleware
php artisan make:middleware MiddlewareName

# Command
php artisan make:command CommandName

# Request (Form Request untuk validasi)
php artisan make:request StoreModelNameRequest

# Test
php artisan make:test ModelNameTest
```

---

### 🧹 Cache & Optimasi

```bash
# Bersihkan semua cache
php artisan optimize:clear

# Atau bersihkan cache individual
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache untuk production (mempercepat aplikasi)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

### 🧪 Testing

```bash
# Jalankan semua test (via Composer, dengan config:clear)
composer test

# Jalankan via Artisan
php artisan test

# Verbose output
php artisan test --verbose

# Filter test tertentu (nama test)
php artisan test --filter "operator can call next ticket"

# Filter berdasarkan file
php artisan test tests/Feature/OperatorTest.php

# Jalankan test group tertentu
php artisan test --group operator

# Tampilkan coverage (butuh Xdebug)
php artisan test --coverage

# Stop saat ada test yang gagal
php artisan test --stop-on-failure
```

---

### 🔍 Debugging & Inspeksi

```bash
# Daftar semua route
php artisan route:list

# Filter route berdasarkan nama
php artisan route:list --name=operator

# Filter berdasarkan path
php artisan route:list --path=admin

# Buka REPL (Tinker) untuk testing manual
php artisan tinker

# Lihat semua command tersedia
php artisan list

# Bantuan untuk command tertentu
php artisan help queue:reset-daily
```

---

### 🔑 Contoh Penggunaan Tinker

```php
// Buka tinker
php artisan tinker

// Buat admin baru
>>> App\Models\User::create(['name'=>'Admin Baru', 'email'=>'admin2@test.com', 'password'=>bcrypt('password'), 'role'=>'admin'])

// Cek jumlah antrian aktif
>>> App\Models\Ticket::where('status', 'waiting')->count()

// Reset counter semua operator
>>> App\Models\Counter::update(['current_operator_id' => null])

// Lihat tiket terbaru
>>> App\Models\Ticket::latest()->first()

// Test estimasi waktu
>>> App\Models\Location::first()->getEstimatedWaitTime(5)

// Generate nomor tiket
>>> App\Models\Location::first()->generateTicketNumber()

// Cek apakah lokasi sedang buka
>>> App\Models\Location::first()->isOpen()
```

---

### 📋 Commands untuk Production (Docker)

```bash
# Jalankan command di dalam container Docker
docker compose exec app php artisan queue:reset-daily
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize

# Lihat logs container
docker compose logs -f app

# Restart container
docker compose restart app

# Rebuild container setelah perubahan kode
docker compose up -d --build

# Masuk ke shell container
docker compose exec app sh
```

---

## Scheduled Tasks (Rekomendasi)

Untuk production, tambahkan cron job berikut di server:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /path/to/antriyuk && php artisan schedule:run >> /dev/null 2>&1
```

Kemudian daftarkan schedule di `app/Console/Kernel.php` (buat jika belum ada):

```php
protected function schedule(Schedule $schedule): void
{
    // Reset antrian setiap tengah malam
    $schedule->command('queue:reset-daily')->dailyAt('00:05');
}
```

---

## Environment Variables Penting

| Variable | Nilai Development | Nilai Production |
|---------|-----------------|----------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `DB_CONNECTION` | `sqlite` | `sqlite` atau `mysql` |
| `BROADCAST_CONNECTION` | `reverb` | `reverb` |
| `QUEUE_CONNECTION` | `database` | `database` |
| `CACHE_STORE` | `database` | `database` |
| `SESSION_DRIVER` | `database` | `database` |
| `LOG_LEVEL` | `debug` | `error` |
