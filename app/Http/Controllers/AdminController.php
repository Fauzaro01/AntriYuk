<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Location;
use App\Models\ServiceCategory;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_locations' => Location::count(),
            'active_locations' => Location::where('is_active', true)->count(),
            'total_operators' => User::where('role', 'operator')->count(),
            'today_tickets' => Ticket::whereDate('created_at', today())->count(),
            'today_completed' => Ticket::whereDate('created_at', today())->where('status', 'completed')->count(),
            'today_waiting' => Ticket::where('status', 'waiting')->count(),
        ];

        $locations = Location::withCount([
            'tickets as today_tickets_count' => fn ($q) => $q->whereDate('created_at', today()),
            'tickets as waiting_count' => fn ($q) => $q->where('status', 'waiting'),
            'tickets as completed_count' => fn ($q) => $q->whereDate('created_at', today())->where('status', 'completed'),
        ])->get();

        // Chart 1: Donut — today's ticket status distribution
        $todayTickets = Ticket::whereDate('created_at', today())->get();
        $donutData = [
            'completed' => $todayTickets->where('status', 'completed')->count(),
            'waiting'   => $todayTickets->whereIn('status', ['waiting', 'calling', 'serving'])->count(),
            'skipped'   => $todayTickets->where('status', 'skipped')->count(),
            'cancelled' => $todayTickets->where('status', 'cancelled')->count(),
        ];

        // Chart 2: Hourly bar — tickets created per hour today (07:00–18:00)
        $hourlyData = array_fill(7, 12, 0); // hours 7..18
        foreach ($todayTickets as $t) {
            $h = (int) $t->created_at->format('G');
            if ($h >= 7 && $h <= 18) {
                $hourlyData[$h]++;
            }
        }
        $hourlyLabels = array_map(fn($h) => sprintf('%02d:00', $h), range(7, 18));
        $hourlyValues = array_values($hourlyData);

        // Chart 3: Per-location bar — completed vs waiting
        $locationChartData = [
            'labels'    => $locations->pluck('name')->toArray(),
            'completed' => $locations->pluck('completed_count')->toArray(),
            'waiting'   => $locations->pluck('waiting_count')->toArray(),
        ];

        return view('admin.dashboard', compact('stats', 'locations', 'donutData', 'hourlyLabels', 'hourlyValues', 'locationChartData'));
    }

    // Location Management
    public function locations(): View
    {
        $locations = Location::withCount('counters', 'users')->get();
        return view('admin.locations.index', compact('locations'));
    }

    public function createLocation(): View
    {
        return view('admin.locations.create');
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:locations,code'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'average_service_time' => ['required', 'integer', 'min:1', 'max:120'],
            'max_daily_tickets' => ['nullable', 'integer', 'min:1'],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
        ]);

        Location::create($validated);

        return redirect()->route('admin.locations')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function editLocation(Location $location): View
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:locations,code,' . $location->id],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'average_service_time' => ['required', 'integer', 'min:1', 'max:120'],
            'max_daily_tickets' => ['nullable', 'integer', 'min:1'],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'is_active' => ['boolean'],
        ]);

        $location->update($validated);

        return redirect()->route('admin.locations')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function deleteLocation(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations')
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    // Counter Management
    public function counters(Location $location): View
    {
        $counters = $location->counters()->with('currentOperator')->get();
        return view('admin.counters.index', compact('location', 'counters'));
    }

    public function storeCounter(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $location->counters()->create($validated);

        return back()->with('success', 'Loket berhasil ditambahkan.');
    }

    public function toggleCounter(Counter $counter): RedirectResponse
    {
        $counter->update(['is_active' => !$counter->is_active]);

        $status = $counter->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Loket berhasil {$status}.");
    }

    public function deleteCounter(Counter $counter): RedirectResponse
    {
        $counter->delete();
        return back()->with('success', 'Loket berhasil dihapus.');
    }

    // User Management
    public function users(): View
    {
        $users = User::with('location')->get();
        $locations = Location::where('is_active', true)->get();
        return view('admin.users.index', compact('users', 'locations'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,operator,viewer'],
            'location_id' => ['nullable', 'exists:locations,id'],
        ]);

        $user->update($validated);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    // Service Category Management
    public function serviceCategories(Location $location): View
    {
        $categories = $location->serviceCategories()->get();
        return view('admin.service-categories.index', compact('location', 'categories'));
    }

    public function storeServiceCategory(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $location->serviceCategories()->create($validated);

        return back()->with('success', 'Kategori layanan berhasil ditambahkan.');
    }

    public function toggleServiceCategory(ServiceCategory $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori layanan berhasil {$status}.");
    }

    public function deleteServiceCategory(ServiceCategory $category): RedirectResponse
    {
        $category->delete();
        return back()->with('success', 'Kategori layanan berhasil dihapus.');
    }

    // Live Monitor
    public function monitor(): View
    {
        $locations = Location::where('is_active', true)
            ->with([
                'counters' => fn ($q) => $q->where('is_active', true)->with('currentOperator'),
                'tickets' => fn ($q) => $q->whereDate('created_at', today())
                    ->whereIn('status', ['waiting', 'calling', 'serving']),
            ])
            ->withCount([
                'tickets as today_total'     => fn ($q) => $q->whereDate('created_at', today()),
                'tickets as today_completed' => fn ($q) => $q->whereDate('created_at', today())->where('status', 'completed'),
                'tickets as today_waiting'   => fn ($q) => $q->where('status', 'waiting'),
                'tickets as today_priority'  => fn ($q) => $q->whereDate('created_at', today())->where('is_priority', true)->where('status', 'waiting'),
            ])
            ->get();

        return view('admin.monitor', compact('locations'));
    }
}
