<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ticket;
use App\Models\Location;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QRCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_qr_route_returns_png()
    {
        $location = Location::factory()->create(['is_active' => true]);
        $ticket = Ticket::factory()->create(['location_id' => $location->id]);

        // No mocking needed; controller returns dummy PNG in testing environment

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.tickets.qr', $ticket));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
    }

    private function adminUser()
    {
        return \App\Models\User::factory()->create(['role' => 'admin']);
    }
}
