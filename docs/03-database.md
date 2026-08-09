# 🗄️ Skema Database — AntriYuk

## Entity Relationship Diagram (ERD)

```
┌─────────────┐         ┌──────────────────┐         ┌────────────────┐
│    users    │         │    locations      │         │   counters     │
├─────────────┤         ├──────────────────┤         ├────────────────┤
│ id          │         │ id               │◄────────│ id             │
│ name        │         │ name             │         │ location_id FK │
│ email       │         │ code (unique)    │         │ name           │
│ password    │         │ description      │         │ is_active      │
│ role        │         │ address          │         │ curr_operator FK│
│ location_id │──────►  │ is_active        │         │ created_at     │
│ created_at  │         │ average_service_ │         │ updated_at     │
│ updated_at  │         │   time (minutes) │         └──────┬─────────┘
└──────┬──────┘         │ open_time        │                │
       │                │ close_time       │                │
       │                │ created_at       │                │
       │                │ updated_at       │                │
       │                └────────┬─────────┘                │
       │                         │                          │
       │              ┌──────────┴────────────────────┐     │
       │              │          │                    │     │
       │              ▼          ▼                    ▼     │
       │   ┌──────────────┐  ┌─────────────────┐  ┌──────┐ │
       │   │   tickets    │  │service_categories│  │      │ │
       │   ├──────────────┤  ├─────────────────┤  │      │ │
       │   │ id           │  │ id              │  │      │ │
       │   │ location_id FK│  │ location_id FK  │  │      │ │
       │   │ counter_id FK│◄─│ name            │  │      │ │
       │   │ served_by FK │◄─│ description     │  │      │ │
       │   │ service_cat FK│  │ is_active       │  │      │ │
       │   │ ticket_number│  │ created_at      │  │      │ │
       │   │ customer_name│  │ updated_at      │  │      │ │
       │   │ customer_phone│ └─────────────────┘  │      │ │
       │   │ status       │                       └──────┘ │
       │   │ called_at    │      ┌─────────────────┐       │
       │   │ served_at    │      │    bookings     │       │
       │   │ completed_at │      ├─────────────────┤       │
       │   │ service_time │      │ id              │       │
       │   │ notes        │◄─────│ ticket_id FK    │       │
       │   │ created_at   │      │ location_id FK  │       │
       │   │ updated_at   │      │ service_cat FK  │       │
       │   └──────┬───────┘      │ customer_name   │       │
       │          │              │ customer_phone  │       │
       │          │              │ booking_date    │       │
       │          │              │ booking_time    │       │
       │          ▼              │ status          │       │
       │   ┌──────────────┐      │ booking_code    │       │
       │   │  feedbacks   │      │ created_at      │       │
       │   ├──────────────┤      │ updated_at      │       │
       │   │ id           │      └─────────────────┘       │
       │   │ ticket_id FK │                                 │
       │   │ rating (1-5) │                                 │
       │   │ comment      │◄────────────────────────────────┘
       │   │ created_at   │
       │   │ updated_at   │
       │   └──────────────┘
       │
       └────────────────────────────────────────────────────────────────
```

---

## Tabel-Tabel Database

### `users`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key (auto increment) |
| `name` | varchar | No | Nama pengguna |
| `email` | varchar | No | Email unik |
| `email_verified_at` | timestamp | Yes | Waktu verifikasi email |
| `password` | varchar | No | Password (hashed bcrypt) |
| `role` | varchar | No | `admin`, `operator`, atau `viewer` |
| `location_id` | bigint unsigned | Yes | FK ke `locations.id` (untuk operator) |
| `remember_token` | varchar | Yes | Token "Remember Me" |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

**Indeks:** `email` (unique)

---

### `locations`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `name` | varchar | No | Nama lokasi (contoh: "Customer Service") |
| `code` | varchar(10) | No | Kode unik (contoh: "CS", "PAY") |
| `description` | text | Yes | Deskripsi lokasi |
| `address` | varchar | Yes | Alamat fisik |
| `is_active` | boolean | No | Default: `true` |
| `average_service_time` | integer | No | Rata-rata waktu layanan dalam menit (default: 5) |
| `open_time` | time | No | Jam buka (default: `08:00`) |
| `close_time` | time | No | Jam tutup (default: `17:00`) |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

**Indeks:** `code` (unique)

---

### `counters`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `location_id` | bigint unsigned | No | FK ke `locations.id` (cascade delete) |
| `name` | varchar | No | Nama counter (contoh: "Loket CS 1") |
| `is_active` | boolean | No | Default: `true` |
| `current_operator_id` | bigint unsigned | Yes | FK ke `users.id` (null on delete) |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

---

### `tickets`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `location_id` | bigint unsigned | No | FK ke `locations.id` (cascade delete) |
| `counter_id` | bigint unsigned | Yes | FK ke `counters.id` (null on delete) |
| `served_by` | bigint unsigned | Yes | FK ke `users.id` (null on delete) |
| `service_category_id` | bigint unsigned | Yes | FK ke `service_categories.id` (null on delete) |
| `ticket_number` | varchar | No | Format: `{CODE}-{###}` (contoh: CS-001) |
| `customer_name` | varchar | Yes | Nama pelanggan (opsional) |
| `customer_phone` | varchar | Yes | Nomor telepon pelanggan (opsional) |
| `status` | enum | No | `waiting`, `calling`, `serving`, `completed`, `skipped`, `cancelled` |
| `called_at` | timestamp | Yes | Waktu pertama dipanggil |
| `served_at` | timestamp | Yes | Waktu mulai dilayani |
| `completed_at` | timestamp | Yes | Waktu layanan selesai/dilewati/dibatalkan |
| `service_time` | integer | Yes | Durasi layanan dalam detik |
| `notes` | text | Yes | Catatan tambahan |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

**Indeks Komposit:**
- `(location_id, status, created_at)` — untuk query antrian per lokasi
- `(ticket_number, created_at)` — untuk pencarian tiket

---

### `service_categories`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `location_id` | bigint unsigned | No | FK ke `locations.id` (cascade delete) |
| `name` | varchar | No | Nama kategori (contoh: "Komplain") |
| `description` | varchar | Yes | Deskripsi kategori |
| `is_active` | boolean | No | Default: `true` |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

---

### `bookings`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `location_id` | bigint unsigned | No | FK ke `locations.id` (cascade delete) |
| `service_category_id` | bigint unsigned | Yes | FK ke `service_categories.id` (null on delete) |
| `customer_name` | varchar | No | Nama pelanggan |
| `customer_phone` | varchar | No | Nomor telepon pelanggan |
| `booking_date` | date | No | Tanggal reservasi |
| `booking_time` | time | No | Jam reservasi |
| `status` | enum | No | `pending`, `confirmed`, `checked_in`, `completed`, `cancelled` |
| `booking_code` | varchar | No | Kode unik booking (contoh: BK-CS-001) |
| `ticket_id` | bigint unsigned | Yes | FK ke `tickets.id` (null on delete) — diisi saat check-in |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

**Indeks:**
- `(location_id, booking_date, status)` — untuk query booking per lokasi per tanggal
- `booking_code` — untuk pencarian booking

---

### `feedbacks`

| Kolom | Tipe | Nullable | Keterangan |
|-------|------|---------|-----------|
| `id` | bigint unsigned | No | Primary key |
| `ticket_id` | bigint unsigned | No | FK ke `tickets.id` (cascade delete) |
| `rating` | integer | No | Rating 1–5 bintang |
| `comment` | text | Yes | Komentar tambahan |
| `created_at` | timestamp | Yes | |
| `updated_at` | timestamp | Yes | |

---

## Relasi Antar Model (Eloquent)

```php
// Location
Location → hasMany(Counter::class)
Location → hasMany(Ticket::class)
Location → hasMany(User::class)
Location → hasMany(ServiceCategory::class)
Location → hasMany(Booking::class)

// Counter
Counter → belongsTo(Location::class)
Counter → belongsTo(User::class, 'current_operator_id')
Counter → hasMany(Ticket::class)

// Ticket
Ticket → belongsTo(Location::class)
Ticket → belongsTo(Counter::class)
Ticket → belongsTo(User::class, 'served_by')
Ticket → belongsTo(ServiceCategory::class)
Ticket → hasOne(Feedback::class)

// User
User → belongsTo(Location::class)
User → hasOne(Counter::class, 'current_operator_id')
User → hasMany(Ticket::class, 'served_by')

// Booking
Booking → belongsTo(Location::class)
Booking → belongsTo(ServiceCategory::class)
Booking → belongsTo(Ticket::class)

// ServiceCategory
ServiceCategory → belongsTo(Location::class)
ServiceCategory → hasMany(Ticket::class)
ServiceCategory → hasMany(Booking::class)

// Feedback
Feedback → belongsTo(Ticket::class)
```

---

## Urutan Migrasi

| # | File Migrasi | Tabel |
|---|-------------|-------|
| 1 | `0001_01_01_000000_create_users_table.php` | users, password_reset_tokens, sessions |
| 2 | `0001_01_01_000001_create_cache_table.php` | cache, cache_locks |
| 3 | `0001_01_01_000002_create_jobs_table.php` | jobs, job_batches, failed_jobs |
| 4 | `2026_01_31_000001_create_locations_table.php` | locations |
| 5 | `2026_01_31_000002_create_counters_table.php` | counters |
| 6 | `2026_01_31_000003_create_tickets_table.php` | tickets |
| 7 | `2026_01_31_000004_add_role_to_users_table.php` | users (alter: +role, +location_id) |
| 8 | `2026_01_31_000005_create_feedbacks_table.php` | feedbacks |
| 9 | `2026_01_31_000006_create_service_categories_table.php` | service_categories |
| 10 | `2026_01_31_000007_add_service_category_to_tickets_table.php` | tickets (alter: +service_category_id) |
| 11 | `2026_01_31_000008_create_bookings_table.php` | bookings |

---

## Data Seeder (Contoh)

Setelah `php artisan db:seed`, database akan berisi:

**Lokasi:**
| Nama | Kode | Counter | Avg Time | Jam |
|------|------|---------|---------|-----|
| Customer Service | CS | 3 (Loket CS 1-3) | 10 menit | 08:00–17:00 |
| Pembayaran | PAY | 4 (Loket Bayar 1-4) | 5 menit | 08:00–16:00 |
| Pendaftaran | REG | 2 (Loket Daftar 1-2) | 15 menit | 08:00–15:00 |

**Kategori Layanan per Lokasi:**
- CS: Informasi Umum, Komplain, Pembukaan Rekening
- PAY: Pembayaran Tagihan, Transfer
- REG: Pendaftaran Baru, Perpanjangan
