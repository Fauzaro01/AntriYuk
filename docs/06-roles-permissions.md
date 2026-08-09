# 🔐 Roles & Permissions — AntriYuk

## Sistem Multi-Role

AntriYuk mengimplementasikan sistem **Role-Based Access Control (RBAC)** sederhana dengan tiga level peran yang tersimpan di kolom `role` pada tabel `users`.

---

## Definisi Role

### 👑 Admin

Admin memiliki akses penuh ke seluruh sistem.

**Kemampuan:**
- Akses semua halaman publik (browse, ambil tiket, booking)
- Akses panel admin (`/admin/*`)
- CRUD lokasi layanan
- Kelola counter per lokasi
- Kelola user (ubah role & assignment lokasi)
- Kelola kategori layanan
- Lihat laporan statistik antrian

**Tidak ada pembatasan lokasi** — admin bisa mengelola semua lokasi.

---

### 👨‍💼 Operator

Operator bertugas di satu lokasi tertentu dan mengelola antrian harian.

**Kemampuan:**
- Akses semua halaman publik
- Akses panel operator (`/operator/*`)
- Lihat dashboard antrian lokasi yang ditugaskan
- Pilih counter untuk bertugas (assign/leave counter)
- Panggil tiket berikutnya
- Mulai melayani tiket
- Selesaikan layanan tiket
- Lewati tiket yang tidak merespons
- Panggil ulang tiket

**Pembatasan:**
- Hanya bisa mengakses data lokasi yang ditugaskan (`location_id`)
- Tidak bisa memanggil tiket di counter yang dipegang operator lain
- Tidak bisa menyelesaikan tiket milik operator lain

---

### 👤 Viewer

Role default untuk pengguna yang baru mendaftar.

**Kemampuan:**
- Akses semua halaman publik
- Browse lokasi, ambil tiket, lihat status tiket, booking

**Tidak bisa:**
- Akses panel operator
- Akses panel admin

---

## Matriks Hak Akses

| Fitur | Viewer | Operator | Admin |
|-------|:------:|:--------:|:-----:|
| Lihat daftar lokasi | ✅ | ✅ | ✅ |
| Ambil tiket antrian | ✅ | ✅ | ✅ |
| Cek status tiket | ✅ | ✅ | ✅ |
| Buat & kelola booking | ✅ | ✅ | ✅ |
| Dashboard Operator | ❌ | ✅ | ❌ |
| Panggil & layani tiket | ❌ | ✅ | ❌ |
| Dashboard Admin | ❌ | ❌ | ✅ |
| CRUD Lokasi | ❌ | ❌ | ✅ |
| Kelola Counter | ❌ | ❌ | ✅ |
| Kelola User | ❌ | ❌ | ✅ |
| Laporan Statistik | ❌ | ❌ | ✅ |

---

## Middleware Autentikasi

### `EnsureUserIsAdmin`

**File:** `app/Http/Middleware/EnsureUserIsAdmin.php`

Melindungi semua route dengan prefix `/admin`. Jika pengguna bukan admin, request akan ditolak dengan **HTTP 403**.

```php
// Cara kerja
if (!auth()->user()->isAdmin()) {
    abort(403);
}
```

**Digunakan di:**
```php
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->group(function () { ... });
```

---

### `EnsureUserIsOperator`

**File:** `app/Http/Middleware/EnsureUserIsOperator.php`

Melindungi semua route dengan prefix `/operator`. Jika pengguna bukan operator, request akan ditolak dengan **HTTP 403**.

```php
// Cara kerja
if (!auth()->user()->isOperator()) {
    abort(403);
}
```

**Digunakan di:**
```php
Route::middleware(['auth', EnsureUserIsOperator::class])
    ->prefix('operator')
    ->group(function () { ... });
```

---

## Method Role di User Model

```php
// app/Models/User.php

public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isOperator(): bool
{
    return $this->role === 'operator';
}

public function isViewer(): bool
{
    return $this->role === 'viewer';
}

public function canManageLocation(Location $location): bool
{
    return $this->isAdmin() 
        || ($this->isOperator() && $this->location_id === $location->id);
}
```

---

## Pembatasan Berbasis Lokasi (Operator)

Selain pembatasan role, operator juga dibatasi berdasarkan **lokasi yang ditugaskan**:

### 1. Hanya bisa memilih counter di lokasi sendiri

```php
// OperatorController::assignCounter()
if ($counter->location_id !== $user->location_id) {
    return back()->with('error', 'Loket tidak valid.');
}
```

### 2. Hanya bisa menyelesaikan tiket yang ditugaskan ke diri sendiri

```php
// OperatorController::completeTicket()
if ($ticket->served_by !== $user->id) {
    return back()->with('error', 'Tiket ini bukan milik Anda.');
}
```

### 3. Operator harus menyelesaikan tiket saat ini sebelum memanggil berikutnya

```php
// OperatorController::callNext()
$currentTicket = $counter->currentTicket();
if ($currentTicket) {
    return back()->with('error', 'Selesaikan tiket saat ini terlebih dahulu.');
}
```

---

## Alur Registrasi & Assignment

```
1. User baru mendaftar via /register
   └─► Role default: 'viewer'

2. Admin login ke /admin/users
   └─► Ubah role menjadi 'operator'
   └─► Assign ke lokasi tertentu (location_id)

3. Operator bisa login dan akses /operator/dashboard
   └─► Harus memiliki location_id yang valid (tidak null)
```

### Error Jika Operator Belum Ditugaskan ke Lokasi

```php
// OperatorController::dashboard()
if (!$location) {
    abort(403, 'Anda belum ditugaskan ke lokasi manapun.');
}
```

---

## Contoh Penggunaan di Blade Templates

```php
{{-- Cek role di view --}}
@if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
@elseif(auth()->user()->isOperator())
    <a href="{{ route('operator.dashboard') }}">Dashboard Operator</a>
@endif

{{-- Cek apakah bisa kelola lokasi --}}
@if(auth()->user()->canManageLocation($location))
    <button>Edit Lokasi</button>
@endif
```

---

## Keamanan Tambahan

| Aspek | Implementasi |
|-------|-------------|
| Password hashing | `bcrypt` via Laravel's `hashed` cast |
| CSRF Protection | Token CSRF di semua form POST/PUT/DELETE |
| Session | Database-backed sessions (`SESSION_DRIVER=database`) |
| Auth middleware | Route group `['auth']` untuk halaman yang butuh login |
