<?php

namespace App\Events;

use App\Models\Counter;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupervisorRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $locationId;
    public string $operatorName;
    public string $counterName;
    public string $message;

    public function __construct(User $operator, Counter $counter, ?string $message = null)
    {
        $this->locationId = $operator->location_id;
        $this->operatorName = $operator->name;
        $this->counterName = $counter->name;
        $this->message = $message ?? 'Memerlukan bantuan supervisor di ' . $counter->name;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('location.' . $this->locationId),
            new Channel('admin.alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'supervisor.requested';
    }
}
