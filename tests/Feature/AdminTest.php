<?php

use App\Models\Location;
use App\Models\User;

test('admin can access dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);
});

test('non-admin cannot access admin dashboard', function () {
    $operator = User::factory()->create(['role' => 'operator']);

    $response = $this->actingAs($operator)->get('/admin/dashboard');

    $response->assertStatus(403);
});

test('admin can create location', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/admin/locations', [
        'name' => 'New Location',
        'code' => 'NEW',
        'description' => 'Test location',
        'average_service_time' => 10,
        'sla_waiting_threshold_minutes' => 15,
        'open_time' => '08:00',
        'close_time' => '17:00',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('locations', [
        'name' => 'New Location',
        'code' => 'NEW',
    ]);
});

test('admin can update location', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/admin/locations/{$location->id}", [
        'name' => 'New Name',
        'code' => $location->code,
        'average_service_time' => 5,
        'sla_waiting_threshold_minutes' => 15,
        'open_time' => '09:00',
        'close_time' => '18:00',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'name' => 'New Name',
    ]);
});

test('admin can delete location', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/locations/{$location->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('locations', [
        'id' => $location->id,
    ]);
});

test('admin can create counter', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create();

    $response = $this->actingAs($admin)->post("/admin/locations/{$location->id}/counters", [
        'name' => 'Loket 1',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('counters', [
        'location_id' => $location->id,
        'name' => 'Loket 1',
    ]);
});

test('admin can update user role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'viewer']);
    $location = Location::factory()->create();

    $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
        'role' => 'operator',
        'location_id' => $location->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'operator',
        'location_id' => $location->id,
    ]);
});
