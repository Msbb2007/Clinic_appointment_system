<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentWasCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $appointment;
    public $cancelledBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Appointment $appointment, string $cancelledBy = 'admin')
    {
        $this->appointment = $appointment;
        $this->cancelledBy = $cancelledBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
