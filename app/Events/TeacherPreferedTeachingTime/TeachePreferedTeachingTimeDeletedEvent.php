<?php

namespace App\Events\TeacherPreferedTeachingTime;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeachePreferedTeachingTimeDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $preferedTeachingTimes;
    protected $schoolBranchId;
    public function __construct($preferedTeachingTimes, $schoolBranchId)
    {
        $this->preferedTeachingTimes = $preferedTeachingTimes;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.teacherPreferedTeachingTime';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'prefered_teaching_time' => $this->preferedTeachingTimes
        ];
    }
}
