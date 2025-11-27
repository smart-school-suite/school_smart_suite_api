<?php

namespace App\Events\StudentTuitionFee;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TuitionFeeTransactionDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    protected $transaction;
    protected $studentId;
    public function __construct($transaction, $studentId)
    {
        $this->transaction = $transaction;
        $this->studentId = $studentId;
    }

        /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
       $channelName = 'student.' . $this->studentId. '.tuitionFeeTransaction';
        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        return [
            'tuition_fee' => $this->transaction
        ];
    }
}
