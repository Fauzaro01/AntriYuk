<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Booking;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Location $location): View
    {
        $serviceCategories = $location->serviceCategories()->where('is_active', true)->get();

        // Generate available time slots (every 30 minutes during operating hours)
        $slots = $this->generateTimeSlots($location);

        return view('bookings.create', compact('location', 'serviceCategories', 'slots'));
    }

    public function store(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
        ]);

        $booking = \Illuminate\Support\Facades\DB::transaction(function () use ($location, $validated) {
            // Count today's bookings with a row lock to prevent race conditions on booking code generation
            $todayCount = Booking::whereDate('created_at', today())
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->count();

            $bookingCode = sprintf('BK-%s-%03d', $location->code, $todayCount + 1);

            return Booking::create([
                'location_id' => $location->id,
                'service_category_id' => $validated['service_category_id'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'booking_code' => $bookingCode,
                'status' => 'confirmed',
            ]);
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Reservasi berhasil dibuat!');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['location', 'serviceCategory', 'ticket']);

        return view('bookings.show', compact('booking'));
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'Reservasi tidak dapat di-check in.');
        }

        if (!$booking->booking_date->isToday()) {
            return back()->with('error', 'Check-in hanya bisa dilakukan pada hari reservasi.');
        }

        $ticket = $booking->checkIn();

        if (!$ticket) {
            return back()->with('error', 'Gagal melakukan check-in.');
        }

        if (!app()->environment('testing')) {
            broadcast(new QueueUpdated($booking->location))->toOthers();
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Check-in berhasil! Tiket antrian Anda: ' . $ticket->ticket_number);
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if (in_array($booking->status, ['checked_in', 'completed'])) {
            return back()->with('error', 'Reservasi tidak dapat dibatalkan.');
        }

        $booking->cancel();

        return back()->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function check(Request $request): View|RedirectResponse
    {
        if ($request->has('booking_code')) {
            $bookingCode = strtoupper(trim($request->booking_code));
            $booking = Booking::where('booking_code', $bookingCode)->first();

            if ($booking) {
                return redirect()->route('bookings.show', $booking);
            }

            return back()->with('error', 'Kode reservasi tidak ditemukan.');
        }

        return view('bookings.check');
    }

    private function generateTimeSlots(Location $location): array
    {
        $slots = [];
        $openTime = \Carbon\Carbon::parse($location->open_time);
        $closeTime = \Carbon\Carbon::parse($location->close_time);

        while ($openTime->lt($closeTime)) {
            $slots[] = $openTime->format('H:i');
            $openTime->addMinutes(30);
        }

        return $slots;
    }
}
