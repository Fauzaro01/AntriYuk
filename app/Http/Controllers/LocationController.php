<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::where('is_active', true)
            ->withCount(['tickets as waiting_count' => function ($query) {
                $query->where('status', 'waiting');
            }])
            ->get();

        return view('locations.index', compact('locations'));
    }

    public function show(Location $location): View
    {
        $location->load(['counters' => function ($query) {
            $query->where('is_active', true)->with('currentOperator');
        }]);

        $waitingTickets = $location->waitingTickets()->get();
        $currentlyServing = $location->tickets()
            ->whereIn('status', ['calling', 'serving'])
            ->with('counter')
            ->get();

        return view('locations.show', compact('location', 'waitingTickets', 'currentlyServing'));
    }

    public function display(Location $location): View
    {
        return view('locations.display', compact('location'));
    }

    public function kiosk(Location $location): View
    {
        if (!$location->isOpen()) {
            return view('tickets.closed', compact('location'));
        }

        $serviceCategories = $location->serviceCategories()->where('is_active', true)->get();
        $waitingCount = $location->waitingTickets()->count();

        return view('locations.kiosk', compact('location', 'serviceCategories', 'waitingCount'));
    }
}
