<?php

namespace App\Events\AdminTuitionFee;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TuitionFeeScheduleDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $schoolBranchId;
    protected $tuitionFeeSchedule;
    public function __construct($schoolBranchId, $tuitionFeeSchedule)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->tuitionFeeSchedule = $tuitionFeeSchedule;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
       $channelName = 'schoolBranch.' . $this->schoolBranchId. '.tuitionFeeShedule';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'tuition_fee_schedule' => $this->tuitionFeeSchedule
        ];
    }
}
