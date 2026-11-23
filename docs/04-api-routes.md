# 🗺️ Route & API — AntriYuk

Semua route AntriYuk terdefinisi di `routes/web.php`. Aplikasi menggunakan **web routes** (bukan API JSON), dengan respons berupa **Blade views** atau **redirect**.

---

## Public Routes (Tanpa Autentikasi)

### Halaman Utama

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/` | closure | `home` | Landing page dengan statistik sistem |

### Lokasi

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/locations` | `LocationController@index` | `locations.index` | Daftar semua lokasi aktif |
| GET | `/locations/{location}` | `LocationController@show` | `locations.show` | Detail lokasi & antrian aktif |
| GET | `/locations/{location}/display` | `LocationController@display` | `locations.display` | Display board untuk TV/kiosk |

### Tiket Antrian

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/tickets/check` | `TicketController@check` | `tickets.check` | Form pencarian tiket |
| GET | `/locations/{location}/ticket` | `TicketController@create` | `tickets.create` | Form ambil tiket |
| POST | `/locations/{location}/ticket` | `TicketController@store` | `tickets.store` | Simpan tiket baru |
| GET | `/tickets/{ticket}` | `TicketController@show` | `tickets.show` | Halaman status tiket |

**Parameter Form (tickets.store):**
```
customer_name    : string|nullable, max:255
customer_phone   : string|nullable, max:20
service_category_id : integer|nullable, exists:service_categories,id
```

### Feedback

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| POST | `/tickets/{ticket}/feedback` | `FeedbackController@store` | `feedback.store` | Submit feedback setelah layanan |

### Booking / Reservasi

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/bookings/check` | `BookingController@check` | `bookings.check` | Form pencarian booking |
| GET | `/locations/{location}/booking` | `BookingController@create` | `bookings.create` | Form buat booking baru |
| POST | `/locations/{location}/booking` | `BookingController@store` | `bookings.store` | Simpan booking |
| GET | `/bookings/{booking}` | `BookingController@show` | `bookings.show` | Detail booking |
| POST | `/bookings/{booking}/check-in` | `BookingController@checkIn` | `bookings.check-in` | Check-in booking → buat tiket |
| POST | `/bookings/{booking}/cancel` | `BookingController@cancel` | `bookings.cancel` | Batalkan booking |

**Parameter Form (bookings.store):**
```
customer_name       : string|required, max:255
customer_phone      : string|required, max:20
booking_date        : date|required, after_or_equal:today
booking_time        : time|required, format:H:i
service_category_id : integer|nullable, exists:service_categories,id
```

---

## Auth Routes (Middleware: `guest`)

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/login` | `AuthController@showLogin` | `login` | Halaman login |
| POST | `/login` | `AuthController@login` | — | Proses login |
| GET | `/register` | `AuthController@showRegister` | `register` | Halaman register |
| POST | `/register` | `AuthController@register` | — | Proses register |
| POST | `/logout` | `AuthController@logout` | `logout` | Logout (middleware: auth) |

---

## Operator Routes (Middleware: `auth`, `EnsureUserIsOperator`)
**Prefix: `/operator`**

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/operator/dashboard` | `OperatorController@dashboard` | `operator.dashboard` | Dashboard operator |
| POST | `/operator/assign-counter` | `OperatorController@assignCounter` | `operator.assign-counter` | Pilih counter untuk bertugas |
| POST | `/operator/leave-counter` | `OperatorController@leaveCounter` | `operator.leave-counter` | Keluar dari counter |
| POST | `/operator/call-next` | `OperatorController@callNext` | `operator.call-next` | Panggil tiket berikutnya |
| POST | `/operator/tickets/{ticket}/start` | `OperatorController@startServing` | `operator.start-serving` | Mulai melayani tiket |
| POST | `/operator/tickets/{ticket}/complete` | `OperatorController@completeTicket` | `operator.complete` | Selesaikan tiket |
| POST | `/operator/tickets/{ticket}/skip` | `OperatorController@skipTicket` | `operator.skip` | Lewati tiket |
| POST | `/operator/tickets/{ticket}/recall` | `OperatorController@recallTicket` | `operator.recall` | Panggil ulang tiket |

**Parameter Form (operator.assign-counter):**
```
counter_id : integer|required, exists:counters,id
```

---

## Admin Routes (Middleware: `auth`, `EnsureUserIsAdmin`)
**Prefix: `/admin`**

### Dashboard

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/dashboard` | `AdminController@dashboard` | `admin.dashboard` | Dashboard admin |

### Manajemen Lokasi

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/locations` | `AdminController@locations` | `admin.locations` | Daftar lokasi |
| GET | `/admin/locations/create` | `AdminController@createLocation` | `admin.locations.create` | Form buat lokasi |
| POST | `/admin/locations` | `AdminController@storeLocation` | `admin.locations.store` | Simpan lokasi |
| GET | `/admin/locations/{location}/edit` | `AdminController@editLocation` | `admin.locations.edit` | Form edit lokasi |
| PUT | `/admin/locations/{location}` | `AdminController@updateLocation` | `admin.locations.update` | Update lokasi |
| DELETE | `/admin/locations/{location}` | `AdminController@deleteLocation` | `admin.locations.delete` | Hapus lokasi |

**Parameter Form (locations.store / locations.update):**
```
name                : string|required, max:255
code                : string|required, max:10, unique:locations,code
description         : string|nullable
address             : string|nullable, max:255
average_service_time: integer|required, min:1, max:120
open_time           : time|required, format:H:i
close_time          : time|required, format:H:i, after:open_time
is_active           : boolean (hanya saat update)
```

### Manajemen Counter

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/locations/{location}/counters` | `AdminController@counters` | `admin.counters` | Daftar counter per lokasi |
| POST | `/admin/locations/{location}/counters` | `AdminController@storeCounter` | `admin.counters.store` | Tambah counter |
| POST | `/admin/counters/{counter}/toggle` | `AdminController@toggleCounter` | `admin.counters.toggle` | Toggle aktif/nonaktif |
| DELETE | `/admin/counters/{counter}` | `AdminController@deleteCounter` | `admin.counters.delete` | Hapus counter |

### Manajemen User

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/users` | `AdminController@users` | `admin.users` | Daftar semua user |
| PUT | `/admin/users/{user}` | `AdminController@updateUser` | `admin.users.update` | Update role & lokasi user |

**Parameter Form (admin.users.update):**
```
role        : string|required, in:admin,operator,viewer
location_id : integer|nullable, exists:locations,id
```

### Laporan

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/reports` | `ReportController@index` | `admin.reports` | Laporan statistik antrian |

**Query Parameters (admin.reports):**
```
period      : string (today|week|month), default: today
location_id : integer|nullable
```

### Manajemen Kategori Layanan

| Method | URI | Controller | Nama Route | Keterangan |
|--------|-----|-----------|-----------|-----------|
| GET | `/admin/locations/{location}/categories` | `AdminController@serviceCategories` | `admin.categories` | Daftar kategori per lokasi |
| POST | `/admin/locations/{location}/categories` | `AdminController@storeServiceCategory` | `admin.categories.store` | Tambah kategori |
| POST | `/admin/categories/{category}/toggle` | `AdminController@toggleServiceCategory` | `admin.categories.toggle` | Toggle aktif/nonaktif |
| DELETE | `/admin/categories/{category}` | `AdminController@deleteServiceCategory` | `admin.categories.delete` | Hapus kategori |

---

## Grup Route & Middleware Summary

```
Public (no middleware)
├── GET  /
├── GET/POST /locations/*
├── GET/POST /tickets/*
├── POST /tickets/{ticket}/feedback
└── GET/POST /bookings/*

Guest (redirect jika sudah login)
├── GET/POST /login
└── GET/POST /register

Auth (harus login)
└── POST /logout

Operator (auth + role=operator)
└── /operator/*

Admin (auth + role=admin)
└── /admin/*
```

---

## Respons Tipe

| Tipe | Kapan |
|------|-------|
| `View` (Blade) | Menampilkan halaman HTML |
| `RedirectResponse` | Setelah aksi POST (PRG pattern) |
| Flash Message | `with('success', ...)`, `with('error', ...)`, `with('info', ...)` |

### Contoh Flash Messages

```php
return back()->with('success', 'Tiket berhasil dibuat!');
return back()->with('error', 'Loket tidak valid.');
return back()->with('info', 'Tidak ada antrian yang menunggu.');
```
