# 🧪 Panduan Testing — AntriYuk

## Framework Testing

AntriYuk menggunakan **[Pest PHP v4](https://pestphp.com)** — framework testing modern untuk PHP dengan sintaks yang ekspresif dan mudah dibaca. Pest berjalan di atas PHPUnit secara internal.

---

## Konfigurasi Testing

### `phpunit.xml`

```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="BROADCAST_CONNECTION" value="log"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="CACHE_STORE" value="array"/>
```

**Poin penting:**
- Database menggunakan **SQLite in-memory** — test tidak mempengaruhi data development
- Queue berjalan **synchronously** — event broadcast diproses langsung tanpa queue worker
- Cache menggunakan **array driver** — setiap test memulai dengan cache kosong
- Broadcasting ke **log** — event tidak benar-benar dikirim ke WebSocket

### `tests/Pest.php`

```php
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');
```

`RefreshDatabase` memastikan database di-reset sebelum setiap test.

---

## Menjalankan Tests

```bash
# Cara 1: Via Composer (dengan config clear)
composer test

# Cara 2: Via Artisan
php artisan test

# Cara 3: Via Pest langsung
./vendor/bin/pest

# Dengan output verbose
php artisan test --verbose

# Filter test tertentu
php artisan test --filter "operator can call next ticket"

# Jalankan file test tertentu
php artisan test tests/Feature/OperatorTest.php

# Jalankan dengan coverage (butuh Xdebug)
php artisan test --coverage
```

---

## Test Suites

### `AuthTest.php` — 8 Tests

Menguji sistem autentikasi.

| Test | Deskripsi |
|------|----------|
| `guest can view login page` | Halaman login dapat diakses |
| `user can login with valid credentials` | Login berhasil dengan kredensial yang benar |
| `user cannot login with wrong password` | Login gagal dengan password salah |
| `user can register` | Registrasi user baru berhasil |
| `user can logout` | Logout berhasil |
| `authenticated user cannot access login page` | Redirect jika sudah login |
| `operator middleware blocks viewer` | Viewer diblokir dari route operator |
| `admin middleware blocks operator` | Operator diblokir dari route admin |

---

### `LocationTest.php` — 8 Tests

Menguji halaman publik terkait lokasi dan tiket.

| Test | Deskripsi |
|------|----------|
| `can view locations list` | Daftar lokasi tampil dengan benar |
| `can view active locations only` | Lokasi nonaktif tidak muncul |
| `can view location detail` | Detail lokasi dapat diakses |
| `can view display board` | Display board dapat diakses |
| `can create ticket for open location` | Ambil tiket di lokasi yang buka |
| `cannot create ticket for closed location` | Tiket tidak bisa diambil jika lokasi tutup |
| `ticket number is generated correctly` | Format nomor tiket benar (CS-001) |
| `can check ticket status` | Status tiket dapat dilihat |

---

### `OperatorTest.php` — 6 Tests

Menguji fungsi operator dalam mengelola antrian.

| Test | Deskripsi |
|------|----------|
| `operator can access dashboard` | Dashboard operator dapat diakses |
| `non-operator cannot access operator dashboard` | Viewer diblokir (403) |
| `operator can assign to counter` | Operator berhasil pilih counter |
| `operator can call next ticket` | Tiket berikutnya berhasil dipanggil |
| `operator can complete ticket` | Tiket berhasil diselesaikan |
| `operator can skip ticket` | Tiket berhasil dilewati |

---

### `AdminTest.php` — 7 Tests

Menguji fungsi admin dalam mengelola sistem.

| Test | Deskripsi |
|------|----------|
| `admin can access dashboard` | Dashboard admin dapat diakses |
| `non-admin cannot access admin dashboard` | Non-admin diblokir (403) |
| `admin can create location` | Lokasi baru berhasil dibuat |
| `admin can update location` | Lokasi berhasil diupdate |
| `admin can delete location` | Lokasi berhasil dihapus |
| `admin can add counter to location` | Counter berhasil ditambahkan |
| `admin can update user role` | Role user berhasil diubah |

---

### `BookingTest.php` — 13 Tests — Booking & Reservasi

Menguji alur pembuatan dan manajemen booking secara menyeluruh.

| Test | Deskripsi |
|------|----------|
| `can view booking check page` | Halaman cek reservasi dapat diakses |
| `can view booking creation form` | Form buat booking dapat diakses |
| `can create a booking` | Booking berhasil dibuat dengan status confirmed |
| `booking gets unique booking code` | Kode booking memiliki format BK-{CODE}-{###} |
| `can create booking with service category` | Booking dengan kategori layanan berhasil |
| `can view booking details` | Detail booking dapat dilihat via booking_code |
| `can check booking by code` | Redirect ke detail booking saat kode valid |
| `invalid booking code shows error` | Flash error saat kode booking tidak ditemukan |
| `can check in confirmed booking on booking date` | Check-in berhasil dan membuat tiket antrian |
| `cannot check in on wrong date` | Check-in gagal jika bukan hari reservasi |
| `cannot check in already checked-in booking` | Duplikat check-in dicegah |
| `can cancel confirmed booking` | Pembatalan booking berhasil |
| `cannot cancel already checked-in booking` | Booking yang sudah check-in tidak bisa dibatalkan |

---

### `FeedbackTest.php` — Feedback

Menguji sistem rating setelah layanan.

| Test | Deskripsi |
|------|----------|
| `can submit feedback for completed ticket` | Feedback berhasil untuk tiket selesai |
| `cannot submit feedback twice` | Duplikasi feedback dicegah |
| `rating must be between 1 and 5` | Validasi rating |

---

### `ServiceCategoryTest.php` — Kategori Layanan

Menguji CRUD kategori layanan oleh admin.

---

### `ReportTest.php` — Laporan

Menguji halaman laporan admin.

| Test | Deskripsi |
|------|----------|
| `admin can view reports` | Halaman laporan dapat diakses |
| `reports can be filtered by period` | Filter periode (today/week/month) berfungsi |
| `reports can be filtered by location` | Filter per lokasi berfungsi |

---

### `ResetDailyQueueTest.php` — Daily Reset

Menguji command reset antrian harian.

---

## Pola Testing

### Pola Dasar

```php
test('deskripsi test', function () {
    // 1. Arrange — setup data
    $location = Location::factory()->create();
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    // 2. Act — lakukan aksi
    $response = $this->actingAs($operator)->post('/operator/call-next');

    // 3. Assert — verifikasi hasil
    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', ['status' => 'calling']);
});
```

### Factory States

```php
// Buat user dengan role tertentu
$admin = User::factory()->create(['role' => 'admin']);
$operator = User::factory()->create(['role' => 'operator', 'location_id' => $location->id]);

// Buat tiket dengan status tertentu
$ticket = Ticket::factory()->create(['status' => 'waiting']);
$ticket = Ticket::factory()->create(['status' => 'calling', 'served_by' => $operator->id]);

// Buat counter
$counter = Counter::factory()->create(['location_id' => $location->id]);
```

### Assertion HTTP

```php
$response->assertStatus(200);       // Status 200 OK
$response->assertStatus(403);       // Akses ditolak
$response->assertRedirect();        // Response redirect
$response->assertRedirect('/login'); // Redirect ke URL tertentu
$response->assertSee('CS-001');     // Text ada di halaman
$response->assertSessionHas('success'); // Flash message ada
```

### Assertion Database

```php
$this->assertDatabaseHas('tickets', [
    'status' => 'calling',
    'counter_id' => $counter->id,
]);

$this->assertDatabaseMissing('tickets', [
    'status' => 'waiting',
]);

$this->assertDatabaseCount('tickets', 5);
```

---

## Menulis Test Baru

### Contoh: Test untuk Fitur Baru (Skip Ticket)

```php
// tests/Feature/OperatorTest.php

test('operator can skip calling ticket', function () {
    // Setup
    $location = Location::factory()->create();
    $counter = Counter::factory()->create(['location_id' => $location->id]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    // Buat tiket dalam status calling
    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'counter_id' => $counter->id,
        'served_by' => $operator->id,
        'status' => 'calling',
    ]);

    // Aksi
    $response = $this->actingAs($operator)
        ->post("/operator/tickets/{$ticket->id}/skip");

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'skipped',
    ]);
});
```

---

## Continuous Integration

AntriYuk siap untuk CI/CD. Untuk integrasi dengan GitHub Actions, tambahkan `.github/workflows/ci.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: sqlite3, pdo_sqlite

      - name: Install dependencies
        run: composer install --no-interaction

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Run tests
        run: composer test
```

---

## Tips Testing

| Tip | Penjelasan |
|-----|-----------|
| Gunakan `RefreshDatabase` | Selalu gunakan agar test terisolasi |
| Test satu hal per test | Setiap test harus fokus pada satu skenario |
| Gunakan factory | Jangan hardcode data, gunakan Eloquent factories |
| Test happy path & unhappy path | Test kasus berhasil DAN kasus gagal |
| Nama test deskriptif | Gunakan kalimat lengkap: "operator can call next ticket" |
| Group related tests | Gunakan `describe()` untuk mengelompokkan test terkait |
