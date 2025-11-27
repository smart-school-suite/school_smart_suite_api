<?php

namespace App\Events\Specialty;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpecialtyCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $specialty;
    protected $schoolBranchId;
    public function __construct($specialty, $schoolBranchId)
    {
        $this->specialty = $specialty;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
       $channelName = 'schoolBranch.' . $this->specialty->school_branch_id . '.specialty';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'specialty' => $this->specialty
        ];
    }
}
