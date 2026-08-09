<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'today');
        $locationId = $request->get('location_id');

        $dateRange = match ($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()],
        };

        $ticketQuery = Ticket::whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()]);

        if ($locationId) {
            $ticketQuery->where('location_id', $locationId);
        }

        $tickets = $ticketQuery->get();

        // General stats
        $stats = [
            'total_tickets' => $tickets->count(),
            'completed' => $tickets->where('status', 'completed')->count(),
            'skipped' => $tickets->where('status', 'skipped')->count(),
            'cancelled' => $tickets->where('status', 'cancelled')->count(),
            'waiting' => $tickets->where('status', 'waiting')->count(),
            'avg_service_time' => round($tickets->where('status', 'completed')->whereNotNull('service_time')->avg('service_time') / 60, 1),
            'avg_wait_time' => $this->calculateAvgWaitTime($tickets),
        ];

        // Per-location breakdown
        $locationStats = Location::withCount([
            'tickets as total_tickets' => fn ($q) => $q->whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()]),
            'tickets as completed_tickets' => fn ($q) => $q->whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()])->where('status', 'completed'),
            'tickets as skipped_tickets' => fn ($q) => $q->whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()])->where('status', 'skipped'),
        ])->get();

        // Hourly distribution for the period
        $hourlyData = $this->getHourlyDistribution($dateRange, $locationId);

        // Operator performance
        $operatorQuery = User::where('role', 'operator');
        if ($locationId) {
            $operatorQuery->where('location_id', $locationId);
        }

        $operatorStats = $operatorQuery
            ->withCount([
                'servedTickets as tickets_served' => fn ($q) => $q->whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()])->where('status', 'completed'),
            ])
            ->with('location')
            ->get()
            ->filter(fn ($user) => $user->tickets_served > 0)
            ->sortByDesc('tickets_served')
            ->values();

        // Feedback summary
        $feedbackQuery = Feedback::whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()]);
        if ($locationId) {
            $feedbackQuery->whereHas('ticket', fn ($q) => $q->where('location_id', $locationId));
        }

        $feedbackStats = [
            'total' => (clone $feedbackQuery)->count(),
            'avg_rating' => round((clone $feedbackQuery)->avg('rating') ?? 0, 1),
        ];

        $locations = Location::where('is_active', true)->get();

        return view('admin.reports.index', compact(
            'stats',
            'locationStats',
            'hourlyData',
            'operatorStats',
            'feedbackStats',
            'locations',
            'period',
            'locationId'
        ));
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'today');
        $locationId = $request->get('location_id');

        $dateRange = match ($period) {
            'today' => [today(), today()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [today(), today()],
        };

        $ticketQuery = Ticket::with(['location', 'serviceCategory', 'servedBy', 'counter'])
            ->whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()]);

        if ($locationId) {
            $ticketQuery->where('location_id', $locationId);
        }

        $tickets = $ticketQuery->orderBy('created_at')->get();

        $filename = sprintf('laporan-antrian-%s-%s.csv', $period, now()->format('Ymd-His'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compliance
            fputs($file, "\xEF\xBB\xBF");
            
            // CSV Headers
            fputcsv($file, [
                'No. Tiket',
                'Lokasi',
                'Kategori Layanan',
                'Nama Pelanggan',
                'Telepon',
                'Status',
                'Operator',
                'Loket',
                'Waktu Dibuat',
                'Waktu Dipanggil',
                'Waktu Dilayani',
                'Waktu Selesai',
                'Waktu Tunggu (Menit)',
                'Durasi Layanan (Detik)'
            ]);

            foreach ($tickets as $t) {
                $waitTime = $t->called_at ? round($t->called_at->diffInSeconds($t->created_at) / 60, 1) : '-';
                
                fputcsv($file, [
                    $t->ticket_number,
                    $t->location->name ?? '-',
                    $t->serviceCategory->name ?? '-',
                    $t->customer_name ?? '-',
                    $t->customer_phone ?? '-',
                    strtoupper($t->status),
                    $t->servedBy->name ?? '-',
                    $t->counter->name ?? '-',
                    $t->created_at->format('Y-m-d H:i:s'),
                    $t->called_at ? $t->called_at->format('Y-m-d H:i:s') : '-',
                    $t->served_at ? $t->served_at->format('Y-m-d H:i:s') : '-',
                    $t->completed_at ? $t->completed_at->format('Y-m-d H:i:s') : '-',
                    $waitTime,
                    $t->service_time ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    private function calculateAvgWaitTime($tickets): float
    {
        $completedTickets = $tickets->where('status', 'completed')->whereNotNull('called_at');

        if ($completedTickets->isEmpty()) {
            return 0;
        }

        $totalWait = $completedTickets->sum(function ($ticket) {
            return $ticket->called_at->diffInSeconds($ticket->created_at);
        });

        return round($totalWait / $completedTickets->count() / 60, 1);
    }

    private function getHourlyDistribution(array $dateRange, ?int $locationId): array
    {
        $query = Ticket::whereBetween('created_at', [$dateRange[0]->startOfDay(), $dateRange[1]->endOfDay()]);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $tickets = $query->get();
        $hourly = array_fill(0, 24, 0);

        foreach ($tickets as $ticket) {
            $hour = (int) $ticket->created_at->format('G');
            $hourly[$hour]++;
        }

        return $hourly;
    }
}
