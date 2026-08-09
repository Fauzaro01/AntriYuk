<?php

use App\Models\Feedback;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\User;

test('admin can access reports page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/reports');

    $response->assertStatus(200);
    $response->assertSee('Laporan');
});

test('non-admin cannot access reports', function () {
    $viewer = User::factory()->create(['role' => 'viewer']);

    $response = $this->actingAs($viewer)->get('/admin/reports');

    $response->assertStatus(403);
});

test('reports show today period by default', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['code' => 'RPT']);

    Ticket::factory()->count(3)->create([
        'location_id' => $location->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($admin)->get('/admin/reports');

    $response->assertStatus(200);
    $response->assertSee('3'); // total tickets
});

test('reports can filter by period', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/reports?period=week');

    $response->assertStatus(200);
});

test('reports can filter by location', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['code' => 'RP2']);

    $response = $this->actingAs($admin)->get("/admin/reports?location_id={$location->id}");

    $response->assertStatus(200);
});

test('reports show feedback statistics', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['code' => 'RP3']);

    $ticket1 = Ticket::factory()->create(['location_id' => $location->id, 'status' => 'completed']);
    $ticket2 = Ticket::factory()->create(['location_id' => $location->id, 'status' => 'completed']);

    Feedback::factory()->create(['ticket_id' => $ticket1->id, 'rating' => 5]);
    Feedback::factory()->create(['ticket_id' => $ticket2->id, 'rating' => 3]);

    $response = $this->actingAs($admin)->get('/admin/reports');

    $response->assertStatus(200);
    $response->assertSee('Feedback Pelanggan');
});

test('reports show operator performance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['code' => 'RP4']);
    $operator = User::factory()->create([
        'role' => 'operator',
        'name' => 'Operator Test',
        'location_id' => $location->id,
    ]);

    Ticket::factory()->count(5)->create([
        'location_id' => $location->id,
        'served_by' => $operator->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($admin)->get('/admin/reports');

    $response->assertStatus(200);
    $response->assertSee('Operator Test');
});

test('reports filter feedback statistics and operator performance by location', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $location1 = Location::factory()->create(['code' => 'LC1']);
    $location2 = Location::factory()->create(['code' => 'LC2']);
    
    $operator1 = User::factory()->create([
        'role' => 'operator',
        'name' => 'Operator Satu',
        'location_id' => $location1->id,
    ]);
    
    $operator2 = User::factory()->create([
        'role' => 'operator',
        'name' => 'Operator Dua',
        'location_id' => $location2->id,
    ]);

    $ticket1 = Ticket::factory()->create(['location_id' => $location1->id, 'status' => 'completed', 'served_by' => $operator1->id]);
    Feedback::factory()->create(['ticket_id' => $ticket1->id, 'rating' => 5]);

    $ticket2 = Ticket::factory()->create(['location_id' => $location2->id, 'status' => 'completed', 'served_by' => $operator2->id]);
    Feedback::factory()->create(['ticket_id' => $ticket2->id, 'rating' => 2]);

    $response = $this->actingAs($admin)->get("/admin/reports?location_id={$location1->id}");

    $response->assertStatus(200);
    $response->assertSee('5'); // Location 1 feedback rating
    $response->assertSee('Operator Satu');
    $response->assertDontSee('Operator Dua');
});

test('admin can export reports to csv', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $location = Location::factory()->create(['code' => 'RPT', 'name' => 'HQ Service']);
    
    Ticket::factory()->create([
        'location_id' => $location->id,
        'ticket_number' => 'RPT-123',
        'customer_name' => 'Alex Doe',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($admin)->get('/admin/reports/export?period=today&location_id=' . $location->id);

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    
    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment; filename="laporan-antrian-today-');
    
    $content = $response->streamedContent();
    expect($content)->toContain('RPT-123');
    expect($content)->toContain('HQ Service');
    expect($content)->toContain('Alex Doe');
});


