<?php

namespace App\Events\AdminSchoolSemester;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolSemesterDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $schoolBranchId;
    protected $schoolSemester;
    public function __construct($schoolBranchId, $schoolSemester)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->schoolSemester = $schoolSemester;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.schoolSemester';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'semester' => $this->schoolSemester
        ];
    }
}
