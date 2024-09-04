<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Examtimetablereleased
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $exam_id;
    public $specialty_id;
    public $level_id;
    public $school_branch_id;
    public function __construct($exam_id, $specialty_id, $school_branch_id, $level_id)
    {
        //
        $this->$exam_id = $exam_id;
        $this->specialty_id = $specialty_id;
        $this->school_branch_id = $school_branch_id;
        $this->level_id = $level_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
