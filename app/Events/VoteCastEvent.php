<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Live tally update for a candidate, derived from election_votes.
 */
class VoteCastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $schoolBranchId,
        public string $electionId,
        public string $positionId,
        public string $candidateId,
        public int $voteCount,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("election.results.{$this->schoolBranchId}.{$this->electionId}")];
    }

    public function broadcastWith(): array
    {
        return [
            'candidate_id' => $this->candidateId,
            'position_id' => $this->positionId,
            'vote_count' => $this->voteCount,
        ];
    }
}
