<?php

use App\Models\Counter;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\User;

test('operator can access dashboard', function () {
    $location = Location::factory()->create();
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $response = $this->actingAs($operator)->get('/operator/dashboard');

    $response->assertStatus(200);
});

test('non-operator cannot access operator dashboard', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);

    $response = $this->actingAs($viewer)->get('/operator/dashboard');

    $response->assertStatus(403);
});

test('operator can assign to counter', function () {
    $location = Location::factory()->create();
    $counter = Counter::factory()->create(['location_id' => $location->id]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $response = $this->actingAs($operator)->post('/operator/assign-counter', [
        'counter_id' => $counter->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('counters', [
        'id' => $counter->id,
        'current_operator_id' => $operator->id,
    ]);
});

test('operator can call next ticket', function () {
    $location = Location::factory()->create();
    $counter = Counter::factory()->create([
        'location_id' => $location->id,
    ]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    // Assign operator to counter
    $counter->update(['current_operator_id' => $operator->id]);

    // Create waiting ticket
    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'status' => 'waiting',
    ]);

    $response = $this->actingAs($operator)->post('/operator/call-next');

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'calling',
        'counter_id' => $counter->id,
        'served_by' => $operator->id,
    ]);
});

test('operator can complete ticket', function () {
    $location = Location::factory()->create();
    $counter = Counter::factory()->create(['location_id' => $location->id]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'counter_id' => $counter->id,
        'served_by' => $operator->id,
        'status' => 'serving',
    ]);

    $response = $this->actingAs($operator)->post("/operator/tickets/{$ticket->id}/complete");

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'completed',
    ]);
});

test('operator can skip ticket', function () {
    $location = Location::factory()->create();
    $counter = Counter::factory()->create(['location_id' => $location->id]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'counter_id' => $counter->id,
        'served_by' => $operator->id,
        'status' => 'calling',
    ]);

    $response = $this->actingAs($operator)->post("/operator/tickets/{$ticket->id}/skip");

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'skipped',
    ]);
});

test('operator can transfer ticket to another service category', function () {
    $location = Location::factory()->create();
    $cat1 = \App\Models\ServiceCategory::factory()->create(['location_id' => $location->id, 'name' => 'CS']);
    $cat2 = \App\Models\ServiceCategory::factory()->create(['location_id' => $location->id, 'name' => 'Pembayaran']);

    $counter = Counter::factory()->create(['location_id' => $location->id]);
    $operator = User::factory()->create([
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $ticket = Ticket::factory()->create([
        'location_id' => $location->id,
        'service_category_id' => $cat1->id,
        'counter_id' => $counter->id,
        'served_by' => $operator->id,
        'status' => 'serving',
    ]);

    $response = $this->actingAs($operator)->post("/operator/tickets/{$ticket->id}/transfer", [
        'service_category_id' => $cat2->id,
        'transfer_reason' => 'Perlu penanganan pembayaran',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'service_category_id' => $cat2->id,
        'transferred_from_category_id' => $cat1->id,
        'transfer_reason' => 'Perlu penanganan pembayaran',
        'status' => 'waiting',
    ]);
});
