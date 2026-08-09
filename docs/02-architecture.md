# 🏗️ Arsitektur Sistem — AntriYuk

## Gambaran Arsitektur

AntriYuk mengikuti arsitektur **monolitik Laravel** dengan layer real-time menggunakan **WebSocket (Laravel Reverb)**. Semua request HTTP diproses oleh Laravel di sisi server, sementara update real-time dikirim ke frontend melalui public broadcast channels.

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT BROWSER                          │
│                                                                  │
│  ┌──────────────┐    ┌──────────────┐    ┌───────────────────┐  │
│  │  Pelanggan   │    │   Operator   │    │    Display Board  │  │
│  │  (Publik)    │    │  Dashboard   │    │   (TV / Kiosk)   │  │
│  └──────┬───────┘    └──────┬───────┘    └────────┬──────────┘  │
│         │                  │                      │              │
│         └──────────────────┼──────────────────────┘              │
│                            │  HTTP + WebSocket                    │
└────────────────────────────┼─────────────────────────────────────┘
                             │
              ┌──────────────┴──────────────┐
              │                             │
              ▼                             ▼
   ┌──────────────────┐          ┌──────────────────────┐
   │   Laravel App    │          │  Laravel Reverb       │
   │   (PHP 8.2+)     │◄────────►│  (WebSocket Server)  │
   │                  │  Event   │                       │
   │  Controllers     │  Broadcast│  Channels:           │
   │  Models          │          │  - location.{id}     │
   │  Events          │          │  - ticket.{id}       │
   │  Middleware      │          └──────────────────────┘
   └────────┬─────────┘
            │
            ▼
   ┌──────────────────┐
   │     Database     │
   │  SQLite / MySQL  │
   │                  │
   │  - locations     │
   │  - counters      │
   │  - tickets       │
   │  - users         │
   │  - bookings      │
   │  - feedbacks     │
   │  - service_      │
   │    categories    │
   └──────────────────┘
```

---

## Layer Aplikasi

### 1. HTTP Layer (Blade + Form)
Request dari browser diproses melalui `routes/web.php` → Middleware → Controller → View (Blade). Semua UI menggunakan **Blade templates** dengan **Tailwind CSS v4** dan **Alpine.js** (CDN).

### 2. Business Logic Layer (Models)
Logic bisnis diletakkan langsung dalam Eloquent Models:

| Model | Business Logic Method |
|-------|----------------------|
| `Location` | `generateTicketNumber()`, `getEstimatedWaitTime()`, `isOpen()` |
| `Ticket` | `call()`, `startServing()`, `complete()`, `skip()`, `cancel()`, `createTicket()` |
| `Counter` | `currentTicket()`, `isAvailable()` |
| `Booking` | `checkIn()`, `cancel()`, `confirm()`, `generateBookingCode()` |

### 3. Real-time Layer (Events + Reverb)
Setiap perubahan status tiket membroadcast event ke channel WebSocket yang relevan.

```
Ticket::call()
    └─► broadcast(new TicketCalled($ticket))
    └─► broadcast(new TicketStatusChanged($ticket))
    └─► Channel: location.{id}, ticket.{id}
```

### 4. Queue Layer
`QUEUE_CONNECTION=database` digunakan untuk memproses broadcast event secara asinkron. Dalam development mode, queue worker dijalankan bersamaan via `composer dev`.

---

## Komponen Utama

### Controllers

| Controller | Tanggung Jawab |
|-----------|---------------|
| `LocationController` | Tampilkan daftar lokasi, detail, dan display board |
| `TicketController` | Ambil tiket, lihat status, cek tiket |
| `OperatorController` | Dashboard operator, kelola tiket & counter |
| `AdminController` | CRUD lokasi, counter, user, kategori layanan |
| `BookingController` | Buat booking, check-in, batalkan |
| `AuthController` | Login, register, logout |
| `FeedbackController` | Submit feedback pasca layanan |
| `ReportController` | Laporan statistik antrian |

### Middleware

| Middleware | Fungsi |
|-----------|--------|
| `EnsureUserIsAdmin` | Blokir non-admin dengan HTTP 403 |
| `EnsureUserIsOperator` | Blokir non-operator dengan HTTP 403 |
| `guest` | Redirect user terautentikasi ke dashboard |

---

## Alur Data Utama

### Alur Pengambilan Tiket

```
1. Pelanggan akses /locations/{location}/ticket
2. TicketController::create() → cek isOpen()
3. Pelanggan submit form (nama, telepon, kategori)
4. TicketController::store() → Ticket::createTicket()
5. broadcast(TicketCreated) → channel location.{id}
6. broadcast(QueueUpdated) → channel location.{id}
7. Redirect ke /tickets/{ticket} (halaman status tiket)
```

### Alur Operator Memanggil Tiket

```
1. Operator klik "Panggil Berikutnya" di dashboard
2. OperatorController::callNext()
3. Ambil tiket waiting tertua di lokasi operator
4. Ticket::call($counter, $operator)
   ├── Update status → 'calling'
   ├── broadcast(TicketCalled) → channel location.{id} + ticket.{id}
   └── broadcast(TicketStatusChanged) → channel location.{id} + ticket.{id}
5. broadcast(QueueUpdated) → update antrian di display board
```

### Alur Booking Check-In

```
1. Pelanggan akses /bookings/{booking}
2. Klik "Check-In" jika hari yang sama
3. BookingController::checkIn()
4. Booking::checkIn() → Ticket::createTicket()
5. Booking status → 'checked_in', ticket_id tersimpan
6. broadcast(QueueUpdated)
7. Redirect ke halaman tiket
```

---

## Real-time Broadcasting

### WebSocket Channels

| Channel | Tipe | Event yang Dikirim |
|---------|------|-------------------|
| `location.{id}` | Public | `ticket.created`, `ticket.called`, `ticket.status.changed`, `queue.updated` |
| `ticket.{id}` | Public | `ticket.called`, `ticket.status.changed` |

### Frontend WebSocket Listener

File `resources/js/echo.js` menginisialisasi koneksi Laravel Echo:

```javascript
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    ...
});
```

Display board mendengarkan event:
```javascript
window.Echo.channel('location.{{ $location->id }}')
    .listen('.ticket.called', (e) => { /* update UI */ })
    .listen('.ticket.status.changed', (e) => { location.reload(); })
    .listen('.queue.updated', (e) => { /* update counter */ });
```

---

## Deployment Architecture (Docker)

```
Docker Container: antriyuk-app
├── PHP-FPM 8.4 (PHP backend)
├── Nginx (web server, port 80)
├── Laravel Reverb (WebSocket, port 8080)
└── Supervisor (process manager)
    ├── php-fpm
    ├── nginx
    ├── reverb
    └── queue:listen
```

### Volume Mounts
- `app_storage` → `/var/www/html/storage` (logs, cache, sessions)
- `app_database` → `/var/www/html/database` (SQLite file)

---

## Estimasi Waktu Tunggu

```php
// Location::getEstimatedWaitTime(int $position): int
$activeCounters = $this->activeCounters()->count();

if ($activeCounters === 0) {
    return $position * $this->average_service_time;
}

return (int) ceil(($position / $activeCounters) * $this->average_service_time);
```

**Contoh:**
- Posisi antrian: 6
- Counter aktif: 3
- Rata-rata waktu layanan: 10 menit
- Estimasi = ceil(6/3) × 10 = **20 menit**

---

## Keputusan Teknis

| Keputusan | Alasan |
|-----------|--------|
| SQLite sebagai default | Mudah setup untuk development, tidak perlu MySQL server |
| Public WebSocket channels | Antrian bersifat publik, tidak perlu autentikasi per tiket |
| Business logic di Model | Memusatkan logika domain agar mudah ditest dan reused |
| Pest PHP (bukan PHPUnit raw) | API lebih ekspresif, lebih mudah dibaca |
| Alpine.js via CDN | Menghindari kompleksitas build untuk interaksi UI sederhana |
| Queue dengan database driver | Tidak membutuhkan Redis untuk broadcast event |
