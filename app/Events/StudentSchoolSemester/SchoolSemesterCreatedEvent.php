<?php

namespace App\Events\StudentSchoolSemester;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolSemesterCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $studentIds;
    protected $schoolSemester;

    public function __construct($studentIds, $schoolSemester)
    {
        $this->studentIds = $studentIds;
        $this->schoolSemester = $schoolSemester;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return collect($this->studentIds)->map(function ($studentId) {
            return new PrivateChannel("student.{$studentId}.schoolSemester");
        })->all();
    }

    /**
     * Data to be sent with the broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'semester' => $this->schoolSemester,
            'message' => 'A new school semester has been created.',
        ];
    }
}
