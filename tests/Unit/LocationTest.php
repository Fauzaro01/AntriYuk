<?php

namespace Tests\Unit;

use App\Models\Location;
use App\Models\Ticket;
use App\Models\Counter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_correctly_determines_if_location_is_open_based_on_time_and_status()
    {
        // Open from 08:00 to 17:00, active
        $location = Location::factory()->create([
            'is_active' => true,
            'open_time' => Carbon::createFromTime(8, 0, 0),
            'close_time' => Carbon::createFromTime(17, 0, 0),
        ]);

        // Test inside open hours
        Carbon::setTestNow(Carbon::createFromTime(9, 30, 0));
        $this->assertTrue($location->isOpen());

        // Test before open
        Carbon::setTestNow(Carbon::createFromTime(7, 59, 0));
        $this->assertFalse($location->isOpen());

        // Test after close
        Carbon::setTestNow(Carbon::createFromTime(17, 1, 0));
        $this->assertFalse($location->isOpen());

        // Inactive location should always be closed
        $location->update(['is_active' => false]);
        Carbon::setTestNow(Carbon::createFromTime(12, 0, 0));
        $this->assertFalse($location->isOpen());
    }

    /** @test */
    public function it_detects_queue_full_based_on_max_daily_tickets()
    {
        $location = Location::factory()->create([
            'max_daily_tickets' => 2,
        ]);

        // No tickets yet – not full
        $this->assertFalse($location->isQueueFull());

        // Create two tickets for today
        Ticket::factory()->count(2)->create([
            'location_id' => $location->id,
            'created_at' => Carbon::today()->addHour(),
        ]);

        $this->assertTrue($location->isQueueFull());
    }

    /** @test */
    public function it_calculates_estimated_wait_time_using_active_counters_and_average_service_time()
    {
        // Location with average service time of 5 minutes
        $location = Location::factory()->create([
            'average_service_time' => 5,
        ]);

        // No active counters – wait time = position * avg minutes
        $this->assertEquals(15, $location->getEstimatedWaitTime(3));

        // Add two active counters
        Counter::factory()->count(2)->create([
            'location_id' => $location->id,
            'is_active' => true,
        ]);

        // Expected wait: ceil((position / activeCounters) * avg)
        $expected = (int) ceil((4 / 2) * 5); // position 4
        $this->assertEquals($expected, $location->getEstimatedWaitTime(4));
    }
}
