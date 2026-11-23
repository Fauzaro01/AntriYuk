# 🎫 Overview — AntriYuk

## Apa itu AntriYuk?

**AntriYuk** adalah sistem manajemen antrian (_Queue Management System_) modern berbasis web yang dibangun dengan **Laravel 12** dan **Laravel Reverb** untuk memberikan update real-time melalui WebSocket. Sistem ini dirancang untuk lokasi layanan publik maupun bisnis seperti bank, rumah sakit, kantor pemerintahan, atau gerai ritel.

---

## 🎯 Tujuan Proyek

| Tujuan | Penjelasan |
|--------|-----------|
| **Efisiensi Antrian** | Mengurangi waktu tunggu pelanggan dengan sistem antrian berbasis nomor tiket |
| **Transparansi** | Pelanggan bisa melihat posisi antrian dan estimasi waktu tunggu secara real-time |
| **Kemudahan Operasional** | Panel operator yang intuitif untuk memanggil dan mengelola tiket |
| **Manajemen Terpusat** | Panel admin untuk mengelola lokasi, counter, dan pengguna dalam satu dashboard |

---

## ✨ Fitur Utama

### Untuk Pelanggan (Publik)
- 🎫 **Ambil Tiket Antrian** — Nomor tiket dengan format unik per lokasi (contoh: `CS-001`, `PAY-042`)
- 📍 **Cek Status Tiket** — Cari dan lihat posisi antrian kapan saja via nomor tiket
- ⏱️ **Estimasi Waktu Tunggu** — Kalkulasi otomatis berdasarkan jumlah counter aktif
- 📅 **Booking / Reservasi** — Daftar antrian di hari tertentu tanpa harus datang langsung
- 🔔 **Notifikasi Real-time** — Update status tiket langsung via WebSocket tanpa refresh

### Untuk Operator
- 👨‍💼 **Dashboard Operator** — Lihat statistik antrian hari ini dan tiket aktif
- 📟 **Pilih Counter** — Operator memilih loket tempat bertugas
- 📣 **Panggil Tiket** — Memanggil tiket berikutnya secara otomatis
- ✅ **Kelola Status Tiket** — Start serving, selesaikan, lewati, atau panggil ulang tiket
- 📊 **Statistik Harian** — Lihat jumlah tiket terlayani, dilewati, dan menunggu

### Untuk Admin
- 🛡️ **Dashboard Admin** — Ringkasan statistik seluruh sistem
- 🏢 **Kelola Lokasi** — CRUD lokasi layanan (buat, lihat, edit, hapus)
- 🪟 **Kelola Counter** — Tambah, aktifkan/nonaktifkan, hapus counter per lokasi
- 👥 **Kelola User** — Atur role dan penugasan lokasi untuk setiap pengguna
- 📑 **Laporan** — Laporan statistik antrian per periode dan per lokasi
- 🏷️ **Kategori Layanan** — Kelola kategori layanan per lokasi

### Fitur Teknis
- 📺 **Display Board** — Halaman khusus untuk TV/kiosk di ruang tunggu dengan auto-refresh 30 detik
- ⚡ **Real-time WebSocket** — Menggunakan Laravel Reverb (channel: `location.{id}`, `ticket.{id}`)
- 🔐 **Multi-Role Authentication** — Tiga level akses: Admin, Operator, Viewer

---

## 🔄 Siklus Hidup Tiket

```
 Pelanggan ambil tiket
         │
         ▼
    [ waiting ]  ─────────────► [ cancelled ]
         │
         ▼ (operator panggil)
    [ calling ]  ─────────────► [ skipped ]
         │
         ▼ (operator mulai layani)
    [ serving ]
         │
         ▼ (selesai)
   [ completed ]
```

| Status | Keterangan |
|--------|-----------|
| `waiting` | Pelanggan mengambil tiket dan sedang menunggu |
| `calling` | Operator memanggil nomor antrian pelanggan |
| `serving` | Pelanggan sedang dilayani di counter |
| `completed` | Layanan selesai |
| `skipped` | Pelanggan tidak merespons panggilan |
| `cancelled` | Tiket dibatalkan oleh pelanggan |

---

## 🛠️ Tech Stack

| Teknologi | Versi | Kegunaan |
|-----------|-------|---------|
| [Laravel](https://laravel.com) | 12 | Backend framework |
| [PHP](https://php.net) | 8.2+ | Server-side language |
| [Laravel Reverb](https://reverb.laravel.com) | 1.7 | WebSocket server real-time |
| [Tailwind CSS](https://tailwindcss.com) | 4 | Utility-first CSS framework |
| [Alpine.js](https://alpinejs.dev) | 3 | Frontend reaktif (via CDN) |
| [Vite](https://vitejs.dev) | 7 | Asset bundler |
| [Pest PHP](https://pestphp.com) | 4 | Testing framework |
| SQLite / MySQL | — | Database |

---

## 📁 Struktur Direktori

```
antriyuk/
├── app/
│   ├── Console/             # Artisan commands
│   ├── Events/              # Broadcasting events (TicketCreated, QueueUpdated, dll.)
│   ├── Http/
│   │   ├── Controllers/     # 9 controller (Admin, Operator, Booking, dll.)
│   │   └── Middleware/      # EnsureUserIsAdmin, EnsureUserIsOperator
│   ├── Models/              # 7 model (Location, Counter, Ticket, User, Booking, Feedback, ServiceCategory)
│   └── Providers/           # Service providers
├── database/
│   ├── factories/           # Factory untuk semua model
│   ├── migrations/          # 11 file migrasi skema database
│   └── seeders/             # DatabaseSeeder dengan data contoh
├── docs/                    # 📚 Dokumentasi proyek (folder ini)
├── resources/
│   ├── css/app.css          # Tailwind CSS v4
│   ├── js/                  # JavaScript (app.js, bootstrap.js, echo.js)
│   └── views/               # Blade templates (UI dalam Bahasa Indonesia)
│       ├── admin/           # Halaman panel admin
│       ├── auth/            # Login & register
│       ├── bookings/        # Halaman booking/reservasi
│       ├── locations/       # Daftar & detail lokasi, display board
│       ├── operator/        # Dashboard operator
│       └── tickets/         # Ambil & cek tiket
├── routes/
│   └── web.php              # Semua route aplikasi (public, auth, operator, admin)
├── tests/
│   └── Feature/             # 10 test suite Pest PHP
├── docker/                  # Konfigurasi Docker (nginx, supervisor, entrypoint)
├── Dockerfile               # Docker image berbasis PHP 8.4-fpm-alpine
├── docker-compose.yml       # Orchestration untuk production Docker
└── composer.json            # PHP dependencies & scripts
```

---

## 📋 Akun Demo

Setelah menjalankan `php artisan db:seed`:

| Role | Email | Password |
|------|-------|---------|
| Admin | `admin@antriyuk.test` | `password` |
| Operator CS | `operator-cs@antriyuk.test` | `password` |
| Operator Pembayaran | `operator-pay@antriyuk.test` | `password` |
| Operator Pendaftaran | `operator-reg@antriyuk.test` | `password` |

---

## 🔗 Tautan Terkait

- [Arsitektur Sistem](./02-architecture.md)
- [Skema Database](./03-database.md)
- [Panduan Instalasi](./07-installation.md)
- [Panduan Testing](./08-testing.md)
