# ⚡ Events & Broadcasting — AntriYuk

## Gambaran Umum

AntriYuk menggunakan **Laravel Reverb** sebagai WebSocket server dan **Laravel Broadcasting** untuk mengirim event real-time ke frontend. Semua event mengimplementasikan interface `ShouldBroadcast` dan menggunakan **public channels** (tidak perlu autentikasi).

---

## WebSocket Channels

| Channel | Tipe | Siapa yang Listen |
|---------|------|------------------|
| `location.{id}` | Public | Display board, halaman detail lokasi, dashboard operator |
| `ticket.{id}` | Public | Halaman status tiket milik pelanggan |

---

## Daftar Events

### 1. `TicketCreated`

**File:** `app/Events/TicketCreated.php`

**Dipicu oleh:** `Ticket::createTicket()` — saat pelanggan ambil tiket baru atau booking check-in.

**Channel:**
- `location.{ticket.location_id}`

**Broadcast Name:** `ticket.created`

**Payload:**
```json
{
  "ticket": {
    "id": 42,
    "ticket_number": "CS-001",
    "customer_name": "Budi Santoso",
    "status": "waiting",
    "position": 3,
    "estimated_wait_time": 15,
    "created_at": "2026-01-31T08:15:00.000Z"
  },
  "location_id": 1,
  "waiting_count": 5
}
```

---

### 2. `TicketCalled`

**File:** `app/Events/TicketCalled.php`

**Dipicu oleh:** `Ticket::call($counter, $operator)` — saat operator memanggil tiket.

**Channels:**
- `location.{ticket.location_id}`
- `ticket.{ticket.id}`

**Broadcast Name:** `ticket.called`

**Payload:**
```json
{
  "ticket": {
    "id": 42,
    "ticket_number": "CS-001",
    "customer_name": "Budi Santoso",
    "status": "calling",
    "called_at": "2026-01-31T08:30:00.000Z"
  },
  "counter": {
    "id": 2,
    "name": "Loket CS 2"
  },
  "location_id": 1
}
```

---

### 3. `TicketStatusChanged`

**File:** `app/Events/TicketStatusChanged.php`

**Dipicu oleh:** Setiap perubahan status tiket:
- `Ticket::call()` (waiting → calling)
- `Ticket::startServing()` (calling → serving)
- `Ticket::complete()` (serving → completed)
- `Ticket::skip()` (calling/serving → skipped)
- `Ticket::cancel()` (any → cancelled)

**Channels:**
- `location.{ticket.location_id}`
- `ticket.{ticket.id}`

**Broadcast Name:** `ticket.status.changed`

**Payload:**
```json
{
  "ticket": {
    "id": 42,
    "ticket_number": "CS-001",
    "customer_name": "Budi Santoso",
    "status": "serving",
    "position": 0,
    "estimated_wait_time": 0,
    "called_at": "2026-01-31T08:30:00.000Z",
    "served_at": "2026-01-31T08:32:00.000Z",
    "completed_at": null,
    "service_time": null
  },
  "counter": {
    "id": 2,
    "name": "Loket CS 2"
  },
  "location_id": 1,
  "waiting_count": 4
}
```

---

### 4. `QueueUpdated`

**File:** `app/Events/QueueUpdated.php`

**Dipicu oleh:**
- `OperatorController::callNext()` — setelah memanggil tiket
- `OperatorController::completeTicket()` — setelah selesai
- `OperatorController::skipTicket()` — setelah dilewati
- `TicketController::store()` — saat tiket baru dibuat
- `BookingController::checkIn()` — saat check-in booking

**Channel:**
- `location.{location.id}`

**Broadcast Name:** `queue.updated`

**Payload:**
```json
{
  "location_id": 1,
  "waiting_count": 4,
  "tickets": [
    {
      "id": 43,
      "ticket_number": "CS-002",
      "customer_name": null,
      "position": 1,
      "estimated_wait_time": 10,
      "created_at": "2026-01-31T08:10:00.000Z"
    },
    {
      "id": 44,
      "ticket_number": "CS-003",
      "customer_name": "Siti Rahayu",
      "position": 2,
      "estimated_wait_time": 20,
      "created_at": "2026-01-31T08:12:00.000Z"
    }
  ]
}
```

---

## Alur Broadcasting

### Ketika Operator Memanggil Tiket

```
OperatorController::callNext()
    │
    ├─► Ticket::call($counter, $operator)
    │       ├─► broadcast(TicketCalled)->toOthers()
    │       │       └─► Channel: location.1, ticket.42
    │       └─► broadcast(TicketStatusChanged)->toOthers()
    │               └─► Channel: location.1, ticket.42
    │
    └─► broadcast(QueueUpdated)->toOthers()
            └─► Channel: location.1
```

### Ketika Pelanggan Ambil Tiket

```
TicketController::store()
    │
    ├─► Ticket::createTicket($location, ...)
    │       └─► broadcast(TicketCreated)->toOthers()
    │               └─► Channel: location.1
    │
    └─► broadcast(QueueUpdated)->toOthers()
            └─► Channel: location.1
```

---

## Konfigurasi WebSocket

### Environment Variables

```dotenv
# Di .env untuk production
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http

# Di .env untuk Vite (frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### Frontend Echo Setup

File: `resources/js/echo.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### Cara Mendengarkan Event di Frontend

```javascript
// Subscribe ke channel lokasi
window.Echo.channel('location.1')
    .listen('.ticket.created', (e) => {
        console.log('Tiket baru:', e.ticket.ticket_number);
    })
    .listen('.ticket.called', (e) => {
        // Tampilkan notifikasi di display board
        showCallingNotification(e.ticket, e.counter);
    })
    .listen('.ticket.status.changed', (e) => {
        // Reload atau update UI
        updateTicketStatus(e.ticket);
    })
    .listen('.queue.updated', (e) => {
        // Update jumlah antrian
        updateWaitingCount(e.waiting_count);
    });

// Subscribe ke channel tiket spesifik (untuk pelanggan)
window.Echo.channel('ticket.42')
    .listen('.ticket.called', (e) => {
        alert(`Nomor ${e.ticket.ticket_number} dipanggil ke ${e.counter.name}`);
    })
    .listen('.ticket.status.changed', (e) => {
        updateMyTicketStatus(e.ticket);
    });
```

---

## Konfigurasi Testing

Saat testing, broadcasting **dinonaktifkan** menggunakan `fake()`:

```php
// File: tests/Pest.php
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');
```

Event mock tersedia melalui `Event::fake()` jika dibutuhkan dalam test spesifik.

---

## Menjalankan WebSocket Server

```bash
# Development
php artisan reverb:start

# Dengan opsi verbose (untuk debug)
php artisan reverb:start --debug

# Production (di dalam Docker, dikelola Supervisor)
# Konfigurasi: docker/supervisord.conf
```

---

## Pertimbangan Skalabilitas

| Aspek | Implementasi Saat Ini | Alternatif Skalabel |
|-------|----------------------|---------------------|
| Queue | Database driver | Redis + Horizon |
| Broadcasting | Reverb (self-hosted) | Pusher (managed) |
| Database | SQLite | MySQL + read replicas |
| WebSocket | Single Reverb instance | Reverb + load balancer |
