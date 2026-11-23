<?php

namespace Tests\Unit;

use App\Events\TicketCalled;
use App\Events\TicketCreated;
use App\Events\TicketStatusChanged;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Counter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Carbon\Carbon;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_computes_position_based_on_waiting_queue()
    {
        $location = Location::factory()->create();
        // Create two waiting tickets before the target ticket
        Ticket::factory()->count(2)->create([
            'location_id' => $location->id,
            'status' => 'waiting',
        ]);
        // Ticket under test
        $ticket = Ticket::factory()->create([
            'location_id' => $location->id,
            'status' => 'waiting',
            'created_at' => Carbon::now()->addSecond(), // later than the previous two
        ]);

        $this->assertEquals(3, $ticket->position);
    }

    /** @test */
    public function it_returns_zero_position_when_not_waiting()
    {
        $ticket = Ticket::factory()->create(['status' => 'completed']);
        $this->assertEquals(0, $ticket->position);
    }

    /** @test */
    public function it_estimates_wait_time_using_location_helper()
    {
        $location = Location::factory()->create();
        $location->method('getEstimatedWaitTimeForCategory')->willReturn(15);
        $ticket = Ticket::factory()->make([
            'location_id' => $location->id,
            'status' => 'waiting',
            'service_category_id' => null,
            'created_at' => now(),
        ]);
        $ticket->setRelation('location', $location);
        $this->assertEquals(15, $ticket->estimated_wait_time);
    }

    /** @test */
    public function call_updates_fields_and_fires_events()
    {
        Event::fake([TicketCalled::class, TicketStatusChanged::class]);
        $counter = Counter::factory()->create();
        $operator = User::factory()->create(['role' => 'operator']);
        $ticket = Ticket::factory()->create(['status' => 'waiting']);

        $ticket->call($counter, $operator);

        $this->assertEquals('calling', $ticket->fresh()->status);
        $this->assertEquals($counter->id, $ticket->counter_id);
        $this->assertEquals($operator->id, $ticket->served_by);
        $this->assertNotNull($ticket->called_at);

        Event::assertDispatched(TicketCalled::class);
        Event::assertDispatched(TicketStatusChanged::class);
    }

    /** @test */
    public function start_serving_changes_status_and_timestamp()
    {
        Event::fake(TicketStatusChanged::class);
        $ticket = Ticket::factory()->create(['status' => 'calling']);

        $ticket->startServing();

        $this->assertEquals('serving', $ticket->fresh()->status);
        $this->assertNotNull($ticket->served_at);
        Event::assertDispatched(TicketStatusChanged::class);
    }

    /** @test */
    public function complete_sets_status_and_calculates_service_time()
    {
        Event::fake(TicketStatusChanged::class);
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $ticket = Ticket::factory()->create([
            'status' => 'serving',
            'served_at' => $now->subMinutes(5),
        ]);

        $ticket->complete();
        $fresh = $ticket->fresh();
        $this->assertEquals('completed', $fresh->status);
        $this->assertEquals(300, $fresh->service_time); // 5 minutes = 300 seconds
        $this->assertNotNull($fresh->completed_at);
        Event::assertDispatched(TicketStatusChanged::class);
    }

    /** @test */
    public function skip_updates_status_and_completed_at()
    {
        Event::fake(TicketStatusChanged::class);
        $ticket = Ticket::factory()->create(['status' => 'waiting']);
        $ticket->skip();
        $fresh = $ticket->fresh();
        $this->assertEquals('skipped', $fresh->status);
        $this->assertNotNull($fresh->completed_at);
        Event::assertDispatched(TicketStatusChanged::class);
    }

    /** @test */
    public function cancel_updates_status_and_completed_at()
    {
        Event::fake(TicketStatusChanged::class);
        $ticket = Ticket::factory()->create(['status' => 'waiting']);
        $ticket->cancel();
        $fresh = $ticket->fresh();
        $this->assertEquals('cancelled', $fresh->status);
        $this->assertNotNull($fresh->completed_at);
        Event::assertDispatched(TicketStatusChanged::class);
    }

    /** @test */
    public function create_ticket_generates_sequential_ticket_number()
    {
        Event::fake(TicketCreated::class);
        $location = Location::factory()->create(['code' => 'L001']);
        // First ticket of the day
        $ticket1 = Ticket::createTicket($location);
        $this->assertEquals('L001-001', $ticket1->ticket_number);
        // Second ticket of the same day
        $ticket2 = Ticket::createTicket($location);
        $this->assertEquals('L001-002', $ticket2->ticket_number);
        Event::assertDispatchedTimes(TicketCreated::class, 2);
    }
}
