<?php

namespace App\Events\Student;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $student;
    protected $schoolBranchId;
    public function __construct($student, $schoolBranchId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->student = $student;
    }

    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.student';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'student' => $this->student
        ];
    }
}
