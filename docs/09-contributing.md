# 🤝 Panduan Kontribusi — AntriYuk

## Selamat Datang, Kontributor!

Kontribusi sangat diterima dan dihargai. Panduan ini menjelaskan cara berkontribusi pada proyek AntriYuk secara efektif, mengikuti pola kode yang sudah ada.

---

## Alur Kontribusi

```
1. Fork repository
2. Clone fork kamu
3. Buat branch fitur/bugfix
4. Implementasi perubahan
5. Tulis tests
6. Jalankan test suite
7. Push ke fork kamu
8. Buat Pull Request
```

### Langkah Detail

```bash
# 1. Fork di GitHub, lalu clone
git clone https://github.com/YOUR-USERNAME/antriyuk.git
cd antriyuk

# 2. Setup project
composer setup
php artisan db:seed

# 3. Buat branch
git checkout -b fitur/nama-fitur-baru
# atau
git checkout -b fix/perbaikan-bug

# 4. Coding...

# 5. Jalankan tests
composer test

# 6. Commit
git add .
git commit -m "feat: tambah fitur X"

# 7. Push
git push origin fitur/nama-fitur-baru

# 8. Buat Pull Request di GitHub
```

---

## Konvensi Naming Branch

| Tipe | Format | Contoh |
|------|--------|--------|
| Fitur baru | `fitur/deskripsi-singkat` | `fitur/notifikasi-sms` |
| Perbaikan bug | `fix/deskripsi-bug` | `fix/timer-estimasi-salah` |
| Perbaikan docs | `docs/deskripsi` | `docs/panduan-docker` |
| Refactoring | `refactor/deskripsi` | `refactor/operator-controller` |

---

## Konvensi Commit Message

Gunakan format [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>: <deskripsi singkat dalam Bahasa Indonesia>

[optional body]
[optional footer]
```

| Type | Kapan |
|------|-------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Perubahan dokumentasi |
| `refactor` | Refactoring kode |
| `test` | Tambah atau perbaiki test |
| `chore` | Perubahan tooling, config, dll. |

**Contoh:**
```
feat: tambah fitur booking online dengan kode reservasi
fix: perbaiki kalkulasi estimasi waktu saat counter 0
docs: perbarui panduan instalasi Docker
test: tambah test untuk fitur booking check-in
```

---

## Cara Menambah Fitur Baru

Ikuti urutan ini saat menambah fitur:

### 1. Buat Migration

```bash
php artisan make:migration create_notifications_table
```

### 2. Buat Model dengan Factory

```bash
php artisan make:model Notification -f
```

Edit model di `app/Models/Notification.php`:
```php
class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'message', 'read_at'];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }
}
```

### 3. Buat Event (Jika Real-time Diperlukan)

```bash
php artisan make:event NotificationSent
```

Event harus implement `ShouldBroadcast`:
```php
class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification) {}

    public function broadcastOn(): array
    {
        return [new Channel('user.' . $this->notification->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }
}
```

### 4. Buat Controller

```bash
php artisan make:controller NotificationController
```

Ikuti pola yang ada:
```php
class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()->notifications()->get();
        return view('notifications.index', compact('notifications'));
    }
}
```

### 5. Tambah Route

Di `routes/web.php`, tambahkan ke grup middleware yang sesuai:
```php
// Untuk user yang login
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
});
```

### 6. Buat Blade View

Di `resources/views/notifications/index.blade.php`.

### 7. Tulis Tests

```bash
# Buat file test
touch tests/Feature/NotificationTest.php
```

```php
test('user can view notifications', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->get('/notifications');
    
    $response->assertStatus(200);
});
```

### 8. Update Seeder (Jika Perlu)

Tambahkan data contoh di `database/seeders/DatabaseSeeder.php`.

---

## Standar Kode

### PHP (PSR-12 + Laravel Conventions)

```php
// ✅ Benar: Gunakan casts() method (bukan $casts property)
protected function casts(): array
{
    return ['is_active' => 'boolean'];
}

// ✅ Benar: Definisikan $fillable secara eksplisit
protected $fillable = ['name', 'email'];

// ✅ Benar: Return type declarations
public function dashboard(): View
public function store(Request $request): RedirectResponse

// ✅ Benar: Validasi di controller, bukan model
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
]);

// ✅ Benar: Business logic di model
// Di Ticket model:
public function call(Counter $counter, User $operator): void
{
    $this->update(['status' => 'calling', ...]);
    broadcast(new TicketCalled($this))->toOthers();
}
```

### Blade Templates

```blade
{{-- ✅ Benar: Gunakan Bahasa Indonesia untuk UI text --}}
<button>Panggil Berikutnya</button>

{{-- ✅ Benar: Pakai flash message untuk feedback --}}
@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- ✅ Benar: Include CSRF token di form --}}
<form method="POST">
    @csrf
    ...
</form>
```

### Testing (Pest PHP)

```php
// ✅ Benar: Nama test deskriptif
test('operator can call next ticket', function () { ... });

// ✅ Benar: Satu assertion per test (fokus)
test('ticket status changes to calling after call', function () {
    // arrange
    $ticket = Ticket::factory()->create(['status' => 'waiting']);
    // act
    $ticket->call($counter, $operator);
    // assert
    expect($ticket->fresh()->status)->toBe('calling');
});

// ✅ Benar: Gunakan factory states
$ticket = Ticket::factory()->create(['status' => 'calling']);

// ❌ Hindari: Data hardcoded
$ticket = Ticket::create([
    'ticket_number' => 'CS-001',
    'location_id' => 1, // hardcoded ID
]);
```

---

## Format Lint & Style

Proyek menggunakan **Laravel Pint** untuk code formatting:

```bash
# Format semua file PHP
./vendor/bin/pint

# Cek tanpa mengubah (dry run)
./vendor/bin/pint --test
```

---

## Checklist Pull Request

Sebelum membuat PR, pastikan:

- [ ] Kode mengikuti standar yang ada
- [ ] Tests ditulis untuk fitur/fix baru
- [ ] Semua tests lulus (`composer test`)
- [ ] Kode di-format dengan Pint
- [ ] Tidak ada debug code (`dd()`, `var_dump()`, `console.log()` yang tersisa)
- [ ] Dokumentasi di `docs/` diperbarui jika relevan
- [ ] Commit message mengikuti konvensi

---

## Melaporkan Bug

Buat GitHub Issue dengan informasi:

1. **Deskripsi bug** — Apa yang terjadi vs yang diharapkan
2. **Langkah reproduksi** — Cara mereproduksi bug
3. **Environment** — PHP version, OS, database
4. **Screenshot/Log** — Jika ada

**Template Issue:**
```markdown
## Deskripsi Bug
[Jelaskan bug yang ditemukan]

## Langkah Reproduksi
1. Buka halaman...
2. Klik tombol...
3. Error terjadi...

## Perilaku yang Diharapkan
[Apa yang seharusnya terjadi]

## Environment
- PHP: 8.2.x
- OS: Windows/macOS/Ubuntu
- DB: SQLite/MySQL

## Screenshot/Log
[Tambahkan screenshot atau error log]
```

---

## Sumber Daya

| Sumber | Link |
|--------|------|
| Laravel 12 Docs | https://laravel.com/docs/12.x |
| Laravel Reverb | https://reverb.laravel.com |
| Pest PHP | https://pestphp.com/docs |
| Tailwind CSS v4 | https://tailwindcss.com/docs |
| Alpine.js | https://alpinejs.dev/start-here |
| Conventional Commits | https://www.conventionalcommits.org |
