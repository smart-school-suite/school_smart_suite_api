<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\ElectionResults;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $result;

    public function __construct(ElectionResults $result)
    {
        $this->result = $result;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('election.results.' . $this->result->school_branch_id . '.' . $this->result->election_id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'candidate_id' => $this->result->candidate_id,
            'position_id' => $this->result->position_id,
            'vote_count' => $this->result->vote_count,
        ];
    }
}
