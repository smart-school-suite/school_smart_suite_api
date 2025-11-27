<?php

namespace App\Events\TeacherAccount;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherAccountDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $teacherId;
    protected $teacher;
    public function __construct($teacherId, $teacher)
    {
       $this->teacherId = $teacherId;
       $this->teacher = $teacher;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->teacherId . '.teacherAccount';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'teacher' => $this->teacher
        ];
    }
}
