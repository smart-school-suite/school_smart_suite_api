<?php

namespace App\Events\StudentTuitionFee;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TuitionFeeScheduleCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $studentId;
    protected $tuitionFeeSchedule;
    public function __construct($studentId, $tuitionFeeSchedule)
    {
        $this->studentId = $studentId;
        $this->tuitionFeeSchedule = $tuitionFeeSchedule;
    }

        /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
       $channelName = 'student.' . $this->studentId. '.tuitionFeeSchedule';
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
