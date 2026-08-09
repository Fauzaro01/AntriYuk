<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsOperator;
use App\Models\Counter;
use App\Models\Location;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    $locations = Location::where('is_active', true)->get();
    $stats = [
        'locations' => Location::where('is_active', true)->count(),
        'counters' => Counter::where('is_active', true)->count(),
        'today_tickets' => Ticket::whereDate('created_at', today())->count(),
    ];
    return view('home', compact('locations', 'stats'));
})->name('home');

// Location routes (public)
Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show');
Route::get('/locations/{location}/display', [LocationController::class, 'display'])->name('locations.display');

// Ticket routes (public)
Route::get('/tickets/check', [TicketController::class, 'check'])->name('tickets.check');
Route::get('/locations/{location}/ticket', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/locations/{location}/ticket', [TicketController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('tickets.store');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

// Feedback routes (public)
Route::post('/tickets/{ticket}/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

// Booking routes (public)
Route::get('/bookings/check', [BookingController::class, 'check'])->name('bookings.check');
Route::get('/locations/{location}/booking', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/locations/{location}/booking', [BookingController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('bookings.store');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Operator routes
Route::middleware(['auth', EnsureUserIsOperator::class])->prefix('operator')->group(function () {
    Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('operator.dashboard');
    Route::post('/assign-counter', [OperatorController::class, 'assignCounter'])->name('operator.assign-counter');
    Route::post('/leave-counter', [OperatorController::class, 'leaveCounter'])->name('operator.leave-counter');
    Route::post('/call-next', [OperatorController::class, 'callNext'])->name('operator.call-next');
    Route::post('/tickets/{ticket}/start', [OperatorController::class, 'startServing'])->name('operator.start-serving');
    Route::post('/tickets/{ticket}/complete', [OperatorController::class, 'completeTicket'])->name('operator.complete');
    Route::post('/tickets/{ticket}/skip', [OperatorController::class, 'skipTicket'])->name('operator.skip');
    Route::post('/tickets/{ticket}/recall', [OperatorController::class, 'recallTicket'])->name('operator.recall');
});

// Admin routes
Route::middleware(['auth', EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Location management
    Route::get('/locations', [AdminController::class, 'locations'])->name('admin.locations');
    Route::get('/locations/create', [AdminController::class, 'createLocation'])->name('admin.locations.create');
    Route::post('/locations', [AdminController::class, 'storeLocation'])->name('admin.locations.store');
    Route::get('/locations/{location}/edit', [AdminController::class, 'editLocation'])->name('admin.locations.edit');
    Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('admin.locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('admin.locations.delete');

    // Counter management
    Route::get('/locations/{location}/counters', [AdminController::class, 'counters'])->name('admin.counters');
    Route::post('/locations/{location}/counters', [AdminController::class, 'storeCounter'])->name('admin.counters.store');
    Route::post('/counters/{counter}/toggle', [AdminController::class, 'toggleCounter'])->name('admin.counters.toggle');
    Route::delete('/counters/{counter}', [AdminController::class, 'deleteCounter'])->name('admin.counters.delete');

    // User management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');

    // Service Category management
    Route::get('/locations/{location}/categories', [AdminController::class, 'serviceCategories'])->name('admin.categories');
    Route::post('/locations/{location}/categories', [AdminController::class, 'storeServiceCategory'])->name('admin.categories.store');
    Route::post('/categories/{category}/toggle', [AdminController::class, 'toggleServiceCategory'])->name('admin.categories.toggle');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteServiceCategory'])->name('admin.categories.delete');
});
