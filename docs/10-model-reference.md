# 📦 Referensi Model — AntriYuk

Dokumentasi lengkap semua Eloquent Model, atributnya, relasi, method bisnis, dan factory states yang tersedia untuk testing.

---

## `Location`

**File:** `app/Models/Location.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `name` | string | Nama lokasi layanan |
| `code` | string (max 10) | Kode unik (contoh: CS, PAY, REG) |
| `description` | string\|null | Deskripsi lokasi |
| `address` | string\|null | Alamat fisik |
| `is_active` | boolean | Status aktif (default: true) |
| `average_service_time` | integer | Rata-rata waktu layanan dalam menit (default: 5) |
| `open_time` | datetime (H:i) | Jam buka (contoh: 08:00) |
| `close_time` | datetime (H:i) | Jam tutup (contoh: 17:00) |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `counters()` | hasMany | Semua counter di lokasi ini |
| `tickets()` | hasMany | Semua tiket di lokasi ini |
| `users()` | hasMany | Semua operator yang ditugaskan |
| `serviceCategories()` | hasMany | Kategori layanan di lokasi ini |
| `bookings()` | hasMany | Semua booking di lokasi ini |
| `activeCounters()` | hasMany (scoped) | Counter yang aktif saja |
| `waitingTickets()` | hasMany (scoped) | Tiket berstatus waiting, urut by created_at |
| `todayTickets()` | hasMany (scoped) | Tiket yang dibuat hari ini |

### Method Bisnis

```php
// Buat nomor tiket berformat {CODE}-{###}
// Contoh: CS-001, PAY-042
public function generateTicketNumber(): string

// Hitung estimasi waktu tunggu (menit) berdasarkan posisi
// Formula: ceil(position / activeCounters) * average_service_time
public function getEstimatedWaitTime(int $position): int

// Cek apakah lokasi sedang buka
// Returns false jika is_active = false atau di luar jam operasional
public function isOpen(): bool
```

### Factory States

```php
// Default: lokasi aktif (is_active = true)
Location::factory()->create()

// Lokasi nonaktif
Location::factory()->inactive()->create()
```

---

## `Counter`

**File:** `app/Models/Counter.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `location_id` | integer | FK ke locations |
| `name` | string | Nama counter (contoh: Loket CS 1) |
| `is_active` | boolean | Status aktif (default: true) |
| `current_operator_id` | integer\|null | FK ke users — operator yang sedang bertugas |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `location()` | belongsTo | Lokasi induk counter ini |
| `currentOperator()` | belongsTo | Operator yang sedang bertugas (via current_operator_id) |
| `tickets()` | hasMany | Semua tiket yang pernah dilayani di counter ini |

### Method Bisnis

```php
// Ambil tiket yang sedang aktif (status calling atau serving)
// Returns null jika tidak ada
public function currentTicket(): ?Ticket

// Cek apakah counter siap menerima tiket berikutnya
// Returns true jika: is_active && ada operator bertugas && tidak ada tiket aktif
public function isAvailable(): bool
```

### Factory States

```php
// Default counter
Counter::factory()->create(['location_id' => $location->id])
```

---

## `Ticket`

**File:** `app/Models/Ticket.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `location_id` | integer | FK ke locations |
| `service_category_id` | integer\|null | FK ke service_categories (opsional) |
| `counter_id` | integer\|null | FK ke counters (diisi saat dipanggil) |
| `served_by` | integer\|null | FK ke users (ID operator yang melayani) |
| `ticket_number` | string | Format: {CODE}-{###} (contoh: CS-001) |
| `customer_name` | string\|null | Nama pelanggan (opsional) |
| `customer_phone` | string\|null | Nomor telepon (opsional) |
| `status` | enum | waiting, calling, serving, completed, skipped, cancelled |
| `called_at` | datetime\|null | Waktu pertama dipanggil operator |
| `served_at` | datetime\|null | Waktu mulai dilayani |
| `completed_at` | datetime\|null | Waktu layanan selesai/dilewati/dibatalkan |
| `service_time` | integer\|null | Durasi layanan dalam detik |
| `notes` | text\|null | Catatan tambahan |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `location()` | belongsTo | Lokasi tempat tiket diambil |
| `counter()` | belongsTo | Counter tempat dilayani |
| `servedBy()` | belongsTo | Operator yang melayani (via served_by) |
| `serviceCategory()` | belongsTo | Kategori layanan yang dipilih |
| `feedback()` | hasOne | Feedback pasca layanan |

### Accessor (Computed Attributes)

```php
// Posisi dalam antrian (0 jika bukan waiting)
$ticket->position;        // integer

// Estimasi waktu tunggu dalam menit (0 jika bukan waiting)
$ticket->estimated_wait_time; // integer
```

### Method Bisnis

```php
// Panggil tiket — ubah ke status 'calling', broadcast TicketCalled + TicketStatusChanged
public function call(Counter $counter, User $operator): void

// Mulai layani — ubah ke 'serving', broadcast TicketStatusChanged
public function startServing(): void

// Selesaikan layanan — ubah ke 'completed', hitung service_time, broadcast
public function complete(): void

// Lewati tiket — ubah ke 'skipped', broadcast TicketStatusChanged
public function skip(): void

// Batalkan tiket — ubah ke 'cancelled', broadcast TicketStatusChanged
public function cancel(): void

// Static factory method — buat tiket baru, broadcast TicketCreated
public static function createTicket(
    Location $location,
    ?string $customerName = null,
    ?string $customerPhone = null,
    ?int $serviceCategoryId = null
): self
```

### Factory States

```php
// Default: status waiting
Ticket::factory()->create(['location_id' => $location->id])

// Status spesifik dengan data lengkap
Ticket::factory()->waiting()->create()
Ticket::factory()->calling()->create()    // + counter_id, served_by, called_at
Ticket::factory()->serving()->create()    // + served_at
Ticket::factory()->completed()->create()  // + completed_at, service_time (480s)
Ticket::factory()->skipped()->create()    // + completed_at
```

---

## `User`

**File:** `app/Models/User.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `name` | string | Nama pengguna |
| `email` | string | Email (unique) |
| `password` | string | Password (auto-hashed via cast) |
| `role` | string | admin, operator, viewer |
| `location_id` | integer\|null | FK ke locations (untuk operator) |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `location()` | belongsTo | Lokasi yang ditugaskan (untuk operator) |
| `operatingCounter()` | hasOne | Counter tempat operator bertugas saat ini (via current_operator_id) |
| `servedTickets()` | hasMany | Semua tiket yang pernah dilayani (via served_by) |

### Method Role

```php
// Cek role pengguna
public function isAdmin(): bool    // role === 'admin'
public function isOperator(): bool // role === 'operator'
public function isViewer(): bool   // role === 'viewer'

// Cek apakah bisa mengelola lokasi tertentu
// Returns true jika admin, atau operator di lokasi tersebut
public function canManageLocation(Location $location): bool
```

### Factory States

```php
// Default: role viewer
User::factory()->create()

// Role spesifik
User::factory()->create(['role' => 'admin'])
User::factory()->create(['role' => 'operator', 'location_id' => $location->id])

// Dengan state khusus (jika didefinisikan di factory)
User::factory()->admin()->create()
User::factory()->operator($location)->create()
```

---

## `Booking`

**File:** `app/Models/Booking.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `location_id` | integer | FK ke locations |
| `service_category_id` | integer\|null | FK ke service_categories |
| `customer_name` | string | Nama pelanggan |
| `customer_phone` | string | Nomor telepon pelanggan |
| `booking_date` | date | Tanggal reservasi |
| `booking_time` | time (H:i) | Jam reservasi |
| `status` | enum | pending, confirmed, checked_in, completed, cancelled |
| `booking_code` | string | Kode unik format BK-{LOC_CODE}-{###} |
| `ticket_id` | integer\|null | FK ke tickets (diisi setelah check-in) |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `location()` | belongsTo | Lokasi tujuan booking |
| `serviceCategory()` | belongsTo | Kategori layanan yang dipilih |
| `ticket()` | belongsTo | Tiket yang dibuat saat check-in |

### Method Bisnis

```php
// Generate kode booking: BK-{LOC_CODE}-{###}
// Contoh: BK-CS-001
public static function generateBookingCode(Location $location): string

// Check-in booking → buat tiket antrian, update status ke 'checked_in'
// Returns Ticket jika berhasil, null jika status tidak valid
public function checkIn(): ?Ticket

// Batalkan booking → update status ke 'cancelled'
public function cancel(): void

// Konfirmasi booking → update status ke 'confirmed'
public function confirm(): void
```

### Siklus Status Booking

```
pending → confirmed → checked_in → (ticket dibuat)
   ↓          ↓
cancelled  cancelled
```

### Factory States

```php
Booking::factory()->pending()->create()
Booking::factory()->confirmed()->create()
Booking::factory()->checkedIn()->create()
Booking::factory()->forToday()->create()       // booking_date = hari ini
Booking::factory()->confirmed()->forToday()->create()
```

---

## `ServiceCategory`

**File:** `app/Models/ServiceCategory.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `location_id` | integer | FK ke locations |
| `name` | string | Nama kategori (contoh: Komplain, Informasi Umum) |
| `description` | string\|null | Deskripsi kategori |
| `is_active` | boolean | Status aktif (default: true) |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `location()` | belongsTo | Lokasi induk kategori |
| `tickets()` | hasMany | Tiket yang memilih kategori ini |
| `bookings()` | hasMany | Booking yang memilih kategori ini |

---

## `Feedback`

**File:** `app/Models/Feedback.php`

### Atribut Fillable

| Atribut | Tipe | Keterangan |
|---------|------|-----------|
| `ticket_id` | integer | FK ke tickets |
| `rating` | integer | Rating bintang 1–5 |
| `comment` | text\|null | Komentar opsional (max 500 karakter) |

### Relasi

| Relasi | Tipe | Keterangan |
|--------|------|-----------|
| `ticket()` | belongsTo | Tiket yang mendapat feedback |

### Aturan Bisnis

- Feedback hanya bisa diberikan untuk tiket berstatus `completed`
- Setiap tiket hanya bisa menerima **satu** feedback (dicegah di FeedbackController)

---

## Ringkasan Factory States per Model

| Model | States Tersedia |
|-------|----------------|
| `Location` | default (active), `inactive()` |
| `Counter` | default |
| `Ticket` | `waiting()`, `calling()`, `serving()`, `completed()`, `skipped()` |
| `User` | default (viewer), set `role` via array |
| `Booking` | `pending()`, `confirmed()`, `checkedIn()`, `forToday()` |
| `ServiceCategory` | default |
| `Feedback` | default |
