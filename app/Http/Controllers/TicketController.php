<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Location;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function create(Location $location): View
    {
        if (!$location->isOpen()) {
            return view('tickets.closed', compact('location'));
        }

        $serviceCategories = $location->serviceCategories()->where('is_active', true)->get();

        return view('tickets.create', compact('location', 'serviceCategories'));
    }

    public function store(Request $request, Location $location): RedirectResponse
    {
        if (!$location->isOpen()) {
            return back()->with('error', 'Lokasi sedang tutup.');
        }

        if ($location->isQueueFull()) {
            return redirect()->route('tickets.full', $location);
        }

        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'is_priority' => ['nullable', 'boolean'],
            'priority_reason' => ['nullable', 'string', 'in:elderly,pregnant,disability'],
        ]);

        $ticket = Ticket::createTicket(
            $location,
            $validated['customer_name'] ?? null,
            $validated['customer_phone'] ?? null,
            $validated['service_category_id'] ?? null,
            !empty($validated['is_priority']),
            $validated['priority_reason'] ?? null
        );

        broadcast(new QueueUpdated($location))->toOthers();

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket antrian berhasil dibuat!');
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['location', 'counter', 'feedback', 'serviceCategory']);

        return view('tickets.show', compact('ticket'));
    }

    public function check(Request $request): View|RedirectResponse
    {
        if ($request->has('ticket_number')) {
            $ticketNumber = strtoupper(trim($request->ticket_number));
            $ticket = Ticket::where('ticket_number', $ticketNumber)
                ->whereDate('created_at', today())
                ->first();

            if ($ticket) {
                return redirect()->route('tickets.show', $ticket);
            }

            return back()->with('error', 'Tiket tidak ditemukan.');
        }

        return view('tickets.check');
    }

    public function full(Location $location): View
    {
        return view('tickets.full', compact('location'));
    }

    public function cancelSelf(Ticket $ticket): RedirectResponse
    {
        if ($ticket->status !== 'waiting') {
            return back()->with('error', 'Tiket ini tidak dapat dibatalkan.');
        }

        $ticket->cancel();

        broadcast(new \App\Events\QueueUpdated($ticket->location))->toOthers();

        return redirect()->route('tickets.show', $ticket)
            ->with('info', 'Tiket Anda telah dibatalkan.');
    }

    public function print(Ticket $ticket): View
    {
        $ticket->load(['location', 'serviceCategory']);
        return view('tickets.print', compact('ticket'));
    }
}
