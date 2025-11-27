<?php

namespace App\Events\StudentAccount;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAccountDeactivatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    protected $student;
    protected $studentId;
    public function __construct($student, $studentId)
    {
        $this->student = $student;
        $this->studentId = $studentId;

    }
      public function broadcastOn(): array
    {
        $channelName = 'student.' . $this->studentId  . '.studentAccount';
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
