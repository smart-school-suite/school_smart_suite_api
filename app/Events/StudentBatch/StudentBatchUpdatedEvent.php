<?php

namespace App\Events\StudentBatch;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentBatchUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $schoolBranchId;
    protected $studentBatch;
    public function __construct($schoolBranchId, $studentBatch)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->studentBatch = $studentBatch;
    }

    public function broadcastOn(): array
    {
        $channelName = 'schoolBranch.' . $this->schoolBranchId . '.studentBatch';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'studentBatch' => $this->studentBatch
        ];
    }
}
