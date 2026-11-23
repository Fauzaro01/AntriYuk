<?php

use App\Models\Booking;
use App\Models\Location;
use App\Models\ServiceCategory;
use App\Models\Ticket;

test('can view booking check page', function () {
    $response = $this->get('/bookings/check');

    $response->assertStatus(200);
    $response->assertSee('Cek Reservasi');
});

test('can view booking creation form', function () {
    $location = Location::factory()->create([
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $response = $this->get("/locations/{$location->id}/booking");

    $response->assertStatus(200);
    $response->assertSee('Buat Reservasi');
});

test('can create a booking', function () {
    $location = Location::factory()->create([
        'code' => 'BK1',
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $response = $this->post("/locations/{$location->id}/booking", [
        'customer_name' => 'Jane Doe',
        'customer_phone' => '08123456789',
        'booking_date' => today()->addDay()->format('Y-m-d'),
        'booking_time' => '09:00',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('bookings', [
        'location_id' => $location->id,
        'customer_name' => 'Jane Doe',
        'customer_phone' => '08123456789',
        'status' => 'confirmed',
    ]);
});

test('booking gets unique booking code', function () {
    $location = Location::factory()->create([
        'code' => 'BK2',
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $this->post("/locations/{$location->id}/booking", [
        'customer_name' => 'User 1',
        'customer_phone' => '08111111111',
        'booking_date' => today()->addDay()->format('Y-m-d'),
        'booking_time' => '09:00',
    ]);

    $booking = Booking::first();
    expect($booking->booking_code)->toStartWith('BK-BK2-');
});

test('can create booking with service category', function () {
    $location = Location::factory()->create([
        'code' => 'BK3',
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);
    $category = ServiceCategory::factory()->create(['location_id' => $location->id]);

    $response = $this->post("/locations/{$location->id}/booking", [
        'customer_name' => 'User 2',
        'customer_phone' => '08222222222',
        'booking_date' => today()->addDay()->format('Y-m-d'),
        'booking_time' => '10:00',
        'service_category_id' => $category->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('bookings', [
        'location_id' => $location->id,
        'service_category_id' => $category->id,
    ]);
});

test('can view booking details', function () {
    $location = Location::factory()->create(['code' => 'BK4']);
    $booking = Booking::factory()->confirmed()->create([
        'location_id' => $location->id,
        'booking_code' => 'BK-BK4-001',
    ]);

    $response = $this->get("/bookings/{$booking->id}");

    $response->assertStatus(200);
    $response->assertSee('BK-BK4-001');
});

test('can check booking by code', function () {
    $location = Location::factory()->create(['code' => 'BK5']);
    $booking = Booking::factory()->confirmed()->create([
        'location_id' => $location->id,
        'booking_code' => 'BK-BK5-001',
    ]);

    $response = $this->get('/bookings/check?booking_code=BK-BK5-001');

    $response->assertRedirect("/bookings/{$booking->id}");
});

test('can check booking by code with lowercase and whitespace', function () {
    $location = Location::factory()->create(['code' => 'BK5']);
    $booking = Booking::factory()->confirmed()->create([
        'location_id' => $location->id,
        'booking_code' => 'BK-BK5-001',
    ]);

    $response = $this->get('/bookings/check?booking_code=%20bk-bk5-001%20');

    $response->assertRedirect("/bookings/{$booking->id}");
});

test('invalid booking code shows error', function () {
    $response = $this->get('/bookings/check?booking_code=INVALID');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('can check in confirmed booking on booking date', function () {
    $location = Location::factory()->create([
        'code' => 'BK6',
        'open_time' => '00:00',
        'close_time' => '23:59',
    ]);
    $booking = Booking::factory()->confirmed()->forToday()->create([
        'location_id' => $location->id,
    ]);

    $response = $this->post("/bookings/{$booking->id}/check-in");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $booking->refresh();
    expect($booking->status)->toBe('checked_in');
    expect($booking->ticket_id)->not->toBeNull();

    $this->assertDatabaseHas('tickets', [
        'id' => $booking->ticket_id,
        'location_id' => $location->id,
        'status' => 'waiting',
    ]);
});

test('cannot check in on wrong date', function () {
    $location = Location::factory()->create(['code' => 'BK7']);
    $booking = Booking::factory()->confirmed()->create([
        'location_id' => $location->id,
        'booking_date' => today()->addDays(3),
    ]);

    $response = $this->post("/bookings/{$booking->id}/check-in");

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $booking->refresh();
    expect($booking->status)->toBe('confirmed');
});

test('cannot check in already checked-in booking', function () {
    $location = Location::factory()->create(['code' => 'BK8']);
    $booking = Booking::factory()->checkedIn()->forToday()->create([
        'location_id' => $location->id,
    ]);

    $response = $this->post("/bookings/{$booking->id}/check-in");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('can cancel confirmed booking', function () {
    $location = Location::factory()->create(['code' => 'BK9']);
    $booking = Booking::factory()->confirmed()->create([
        'location_id' => $location->id,
    ]);

    $response = $this->post("/bookings/{$booking->id}/cancel");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $booking->refresh();
    expect($booking->status)->toBe('cancelled');
});

test('cannot cancel already checked-in booking', function () {
    $location = Location::factory()->create(['code' => 'BKA']);
    $booking = Booking::factory()->checkedIn()->create([
        'location_id' => $location->id,
    ]);

    $response = $this->post("/bookings/{$booking->id}/cancel");

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $booking->refresh();
    expect($booking->status)->toBe('checked_in');
});

test('booking requires customer name', function () {
    $location = Location::factory()->create([
        'code' => 'BKB',
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $response = $this->post("/locations/{$location->id}/booking", [
        'customer_phone' => '08123456789',
        'booking_date' => today()->addDay()->format('Y-m-d'),
        'booking_time' => '09:00',
    ]);

    $response->assertSessionHasErrors('customer_name');
});

test('booking date must be today or later', function () {
    $location = Location::factory()->create([
        'code' => 'BKC',
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $response = $this->post("/locations/{$location->id}/booking", [
        'customer_name' => 'Test',
        'customer_phone' => '08123456789',
        'booking_date' => today()->subDay()->format('Y-m-d'),
        'booking_time' => '09:00',
    ]);

    $response->assertSessionHasErrors('booking_date');
});
