<?php

namespace App\Events\TeacherSchoolSemester;

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

    public $teacherIds;
    public $schoolSemester;

    /**
     * Create a new event instance.
     */
    public function __construct($teacherIds, $schoolSemester)
    {
        $this->teacherIds = is_array($teacherIds) ? $teacherIds : [$teacherIds];
        $this->schoolSemester = $schoolSemester;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * This will broadcast to each teacher's private channel
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return collect($this->teacherIds)->map(function ($teacherId) {
            return new PrivateChannel("teacher.{$teacherId}.schoolSemester");
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
