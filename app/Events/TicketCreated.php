<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class TicketCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcastConnection = 'reverb';

    public function __construct(
        public Ticket $ticket
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('dashboard.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.created';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket' => [
                'id' => $this->ticket->id,
                'ticket_number' => $this->ticket->ticket_number,
                'customer_name' => $this->ticket->customer_name,
                'status' => $this->ticket->status,
                'position' => $this->ticket->position,
                'estimated_wait_time' => $this->ticket->estimated_wait_time,
                'created_at' => $this->ticket->created_at->toISOString(),
            ],
            'location_id' => $this->ticket->location_id,
            'waiting_count' => $this->ticket->location->waitingTickets()->count(),
        ];
    }
}
