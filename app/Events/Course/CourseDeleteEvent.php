<?php

namespace App\Events\Course;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseDeleteEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $course;
    protected $schoolBranchId;
    public function __construct($course, $schoolBranchId)
    {
        $this->course = $course;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.course';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'course' => $this->course
        ];
    }
}
