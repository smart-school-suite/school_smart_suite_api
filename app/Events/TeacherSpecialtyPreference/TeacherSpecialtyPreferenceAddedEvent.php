<?php

namespace App\Events\TeacherSpecialtyPreference;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherSpecialtyPreferenceAddedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $schoolBranchId;
    protected $teacherSpecialtyPreference;
    public function __construct($schoolBranchId, $teacherSpecialtyPreference)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->teacherSpecialtyPreference = $teacherSpecialtyPreference;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.teacherSpecialtyPreference';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'teacher_specialty_preference' => $this->teacherSpecialtyPreference
        ];
    }
}
