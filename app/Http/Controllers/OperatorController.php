<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\AuditLog;
use App\Models\Counter;
use App\Models\Location;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $location = $user->location;

        if (!$location) {
            abort(403, 'Anda belum ditugaskan ke lokasi manapun.');
        }

        $counter = $user->operatingCounter;
        $currentTicket = $counter?->currentTicket();

        $waitingTickets = $location->waitingTickets()->get();
        $todayStats = [
            'served' => $location->todayTickets()->where('status', 'completed')->count(),
            'waiting' => $waitingTickets->count(),
            'skipped' => $location->todayTickets()->where('status', 'skipped')->count(),
        ];

        $counters = $location->counters()->where('is_active', true)->with('currentOperator')->get();
        $serviceCategories = $location->serviceCategories()->where('is_active', true)->get();

        return view('operator.dashboard', compact(
            'location',
            'counter',
            'currentTicket',
            'waitingTickets',
            'todayStats',
            'counters',
            'serviceCategories'
        ));
    }

    public function assignCounter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'counter_id' => ['required', 'exists:counters,id'],
        ]);

        $user = auth()->user();
        $counter = Counter::findOrFail($validated['counter_id']);

        // Check if counter belongs to user's location
        if ($counter->location_id !== $user->location_id) {
            return back()->with('error', 'Loket tidak valid.');
        }

        // Check if counter is already occupied
        if ($counter->current_operator_id && $counter->current_operator_id !== $user->id) {
            return back()->with('error', 'Loket sudah digunakan operator lain.');
        }

        // Remove user from previous counter if any
        Counter::where('current_operator_id', $user->id)->update(['current_operator_id' => null]);

        // Assign user to new counter
        $counter->update(['current_operator_id' => $user->id]);

        return back()->with('success', 'Berhasil masuk ke ' . $counter->name);
    }

    public function leaveCounter(): RedirectResponse
    {
        $user = auth()->user();

        Counter::where('current_operator_id', $user->id)->update(['current_operator_id' => null]);

        return back()->with('success', 'Berhasil keluar dari loket.');
    }

    public function callNext(): RedirectResponse
    {
        $user = auth()->user();
        $counter = $user->operatingCounter;

        if (!$counter) {
            return back()->with('error', 'Anda belum memilih loket.');
        }

        // Check if already serving
        $currentTicket = $counter->currentTicket();
        if ($currentTicket) {
            return back()->with('error', 'Selesaikan tiket saat ini terlebih dahulu.');
        }

        // Get next waiting ticket safely using a transaction and lockForUpdate
        $nextTicket = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $counter) {
            $ticket = Ticket::where('location_id', $user->location_id)
                ->where('status', 'waiting')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if ($ticket) {
                $ticket->call($counter, $user);
            }

            return $ticket;
        });

        if (!$nextTicket) {
            return back()->with('info', 'Tidak ada antrian yang menunggu.');
        }

        AuditLog::log('ticket.call', 'Memanggil tiket ' . $nextTicket->ticket_number . ' ke ' . $counter->name, [
            'ticket_id' => $nextTicket->id,
            'ticket_number' => $nextTicket->ticket_number,
            'counter_id' => $counter->id,
        ]);

        broadcast(new QueueUpdated($user->location))->toOthers();

        return back()->with('success', 'Memanggil tiket ' . $nextTicket->ticket_number);
    }

    public function startServing(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if ($ticket->served_by !== $user->id) {
            return back()->with('error', 'Tiket ini bukan milik Anda.');
        }

        if ($ticket->status !== 'calling') {
            return back()->with('error', 'Status tiket tidak valid.');
        }

        $ticket->startServing();

        AuditLog::log('ticket.start_serving', 'Mulai melayani tiket ' . $ticket->ticket_number, [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
        ]);

        return back()->with('success', 'Mulai melayani tiket ' . $ticket->ticket_number);
    }

    public function completeTicket(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if ($ticket->served_by !== $user->id) {
            return back()->with('error', 'Tiket ini bukan milik Anda.');
        }

        if (!in_array($ticket->status, ['calling', 'serving'])) {
            return back()->with('error', 'Status tiket tidak valid.');
        }

        $ticket->complete();

        AuditLog::log('ticket.complete', 'Menyelesaikan layanan tiket ' . $ticket->ticket_number, [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'service_time' => $ticket->service_time,
        ]);

        broadcast(new QueueUpdated($user->location))->toOthers();

        return back()->with('success', 'Tiket ' . $ticket->ticket_number . ' selesai.');
    }

    public function skipTicket(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if ($ticket->served_by !== $user->id) {
            return back()->with('error', 'Tiket ini bukan milik Anda.');
        }

        if (!in_array($ticket->status, ['calling', 'serving'])) {
            return back()->with('error', 'Status tiket tidak valid.');
        }

        $ticket->skip();

        AuditLog::log('ticket.skip', 'Melewati tiket ' . $ticket->ticket_number, [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
        ]);

        broadcast(new QueueUpdated($user->location))->toOthers();

        return back()->with('info', 'Tiket ' . $ticket->ticket_number . ' dilewati.');
    }

    public function recallTicket(Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        $counter = $user->operatingCounter;

        if (!$counter) {
            return back()->with('error', 'Anda belum memilih loket.');
        }

        if ($ticket->status !== 'calling' || $ticket->served_by !== $user->id) {
            return back()->with('error', 'Tidak dapat memanggil ulang tiket ini.');
        }

        // Re-broadcast the call event
        $ticket->call($counter, $user);

        AuditLog::log('ticket.recall', 'Memanggil ulang tiket ' . $ticket->ticket_number, [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
        ]);

        return back()->with('success', 'Memanggil ulang tiket ' . $ticket->ticket_number);
    }

    public function transferTicket(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();

        if ($ticket->location_id !== $user->location_id) {
            return back()->with('error', 'Tiket dari lokasi lain.');
        }

        $validated = $request->validate([
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'transfer_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $newCategory = \App\Models\ServiceCategory::findOrFail($validated['service_category_id']);

        $ticket->transferToCategory($newCategory, $validated['transfer_reason'] ?? null);

        AuditLog::log('ticket.transfer', 'Mentransfer tiket ' . $ticket->ticket_number . ' ke kategori ' . $newCategory->name, [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'new_category' => $newCategory->name,
            'reason' => $validated['transfer_reason'] ?? null,
        ]);

        broadcast(new QueueUpdated($user->location))->toOthers();

        return back()->with('success', 'Tiket ' . $ticket->ticket_number . ' berhasil dipindahkan ke ' . $newCategory->name);
    }

    public function requestSupervisor(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $counter = $user->operatingCounter;

        if (!$counter) {
            return back()->with('error', 'Anda belum memilih loket.');
        }

        $reason = $request->input('reason', 'Memerlukan bantuan supervisor.');

        broadcast(new \App\Events\SupervisorRequested($user, $counter, $reason))->toOthers();

        AuditLog::log('supervisor.request', 'Meminta bantuan supervisor di ' . $counter->name . ': ' . $reason, [
            'counter_id' => $counter->id,
            'reason' => $reason,
        ]);

        return back()->with('success', 'Permintaan bantuan supervisor telah dikirimkan secara real-time.');
    }
}
