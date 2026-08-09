<?php

use App\Models\Location;
use App\Models\Ticket;

test('can view locations list', function () {
    Location::factory()->create(['name' => 'Customer Service', 'code' => 'CS1']);

    $response = $this->get('/locations');

    $response->assertStatus(200);
    $response->assertSee('Customer Service');
});

test('can view single location', function () {
    $location = Location::factory()->create(['name' => 'Payment', 'code' => 'PAY1']);

    $response = $this->get("/locations/{$location->id}");

    $response->assertStatus(200);
    $response->assertSee('Payment');
});

test('can view ticket creation form', function () {
    $location = Location::factory()->create([
        'open_time' => '00:00',
        'close_time' => '23:59',
    ]);

    $response = $this->get("/locations/{$location->id}/ticket");

    $response->assertStatus(200);
    $response->assertSee('Ambil Tiket');
});

test('can create a ticket', function () {
    $location = Location::factory()->create([
        'code' => 'CS',
        'open_time' => '00:00',
        'close_time' => '23:59',
    ]);

    $response = $this->post("/locations/{$location->id}/ticket", [
        'customer_name' => 'John Doe',
        'customer_phone' => '08123456789',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'location_id' => $location->id,
        'customer_name' => 'John Doe',
        'customer_phone' => '08123456789',
        'status' => 'waiting',
    ]);
});

test('ticket number follows location code pattern', function () {
    $location = Location::factory()->create([
        'code' => 'CS',
        'open_time' => '00:00',
        'close_time' => '23:59',
    ]);

    $this->post("/locations/{$location->id}/ticket", []);

    $ticket = Ticket::first();

    expect($ticket->ticket_number)->toStartWith('CS-');
});

test('can view ticket status', function () {
    $location = Location::factory()->create(['code' => 'CS2']);
    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'ticket_number' => 'CS-001',
    ]);

    $response = $this->get("/tickets/{$ticket->id}");

    $response->assertStatus(200);
    $response->assertSee('CS-001');
});

test('can check ticket by number', function () {
    $location = Location::factory()->create(['code' => 'CS3']);
    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'ticket_number' => 'CS-099',
    ]);

    $response = $this->get('/tickets/check?ticket_number=CS-099');

    $response->assertRedirect("/tickets/{$ticket->id}");
});

test('can check ticket by number with lowercase and whitespace', function () {
    $location = Location::factory()->create(['code' => 'CS3']);
    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'ticket_number' => 'CS-099',
    ]);

    $response = $this->get('/tickets/check?ticket_number=%20cs-099%20');

    $response->assertRedirect("/tickets/{$ticket->id}");
});

test('displays location as closed outside operating hours', function () {
    $location = Location::factory()->create([
        'open_time' => '23:00',
        'close_time' => '23:30',
    ]);

    // If current time is outside 23:00-23:30, it should show closed
    if (now()->format('H:i') < '23:00' || now()->format('H:i') > '23:30') {
        $response = $this->get("/locations/{$location->id}/ticket");
        $response->assertSee('Tutup');
    } else {
        $this->assertTrue(true);
    }
});

test('location calculates estimated wait time dynamically based on completed tickets today', function () {
    $location = Location::factory()->create([
        'average_service_time' => 10, // static default
    ]);

    // Active counters count = 2
    \App\Models\Counter::factory()->create(['location_id' => $location->id, 'is_active' => true]);
    \App\Models\Counter::factory()->create(['location_id' => $location->id, 'is_active' => true]);

    // Scenario 1: No completed tickets today
    // Position 4 waiting ticket should use static default: 10 minutes.
    // ceil(4 / 2) * 10 = 2 * 10 = 20 minutes
    expect($location->getEstimatedWaitTime(4))->toBe(20);

    // Scenario 2: With completed tickets today
    // Let's create some completed tickets today with service_time
    // Ticket 1: 180 seconds (3 minutes)
    // Ticket 2: 420 seconds (7 minutes)
    // Average = (3 + 7) / 2 = 5 minutes
    Ticket::factory()->completed()->create([
        'location_id' => $location->id,
        'created_at' => now(),
        'service_time' => 180, // 3 minutes
    ]);

    Ticket::factory()->completed()->create([
        'location_id' => $location->id,
        'created_at' => now(),
        'service_time' => 420, // 7 minutes
    ]);

    // Position 4 waiting ticket should now use dynamic average: 5 minutes.
    // ceil(4 / 2) * 5 = 2 * 5 = 10 minutes
    expect($location->getCurrentAverageServiceTime())->toBe(5);
    expect($location->getEstimatedWaitTime(4))->toBe(10);
});

