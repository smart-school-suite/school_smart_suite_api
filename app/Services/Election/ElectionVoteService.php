<?php

namespace App\Services\Election;

use App\Events\VoteCastEvent;
use App\Models\Election\Election;
use App\Models\Election\ElectionCandidate;
use App\Models\Election\ElectionRole;
use App\Models\Election\ElectionVote;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ElectionVoteService
{
    use FindsElectionRecords;

    public function __construct(
        private ElectionService $electionService,
        private ElectionEligibilityService $eligibility,
    ) {}

    /**
     * Cast a ballot. $ballot is a list of ['position_id' => .., 'candidate_id' => ..];
     * a voter may choose up to `seats` distinct candidates per position and can
     * vote for each position only once.
     */
    public function castVote(string $electionId, array $ballot, object $currentSchool, Model $voter): array
    {
        if (!ElectionParticipantType::isSupported($voter) || $voter->school_branch_id !== $currentSchool->id) {
            $this->fail("Not allowed", "The authenticated user cannot vote in this school branch.", 403);
        }

        $votes = DB::transaction(function () use ($electionId, $ballot, $currentSchool, $voter) {
            $election = $this->findInBranch(Election::class, $electionId, $currentSchool);
            $this->electionService->sync($election);

            if (!$election->isVotingWindowOpen()) {
                $this->fail("Voting closed", "Voting is not currently open for this election.", 409);
            }

            // Locking the voter's participant row serialises concurrent ballots
            // from the same voter, so per-position limits cannot be raced.
            $participant = $this->eligibility->participantRecord($election, $voter, true);
            if (!$participant?->can_vote) {
                $this->fail("Not eligible", "You are not eligible to vote in this election.", 403);
            }

            $byPosition = collect($ballot)->groupBy('position_id');
            $positions = ElectionRole::where('school_branch_id', $currentSchool->id)
                ->where('election_type_id', $election->election_type_id)
                ->where('status', 'active')
                ->whereIn('id', $byPosition->keys())
                ->get()
                ->keyBy('id');

            $candidates = ElectionCandidate::where('election_id', $election->id)
                ->whereIn('id', collect($ballot)->pluck('candidate_id'))
                ->get()
                ->keyBy('id');

            $alreadyVoted = ElectionVote::where('election_id', $election->id)
                ->where('votable_type', $voter->getMorphClass())
                ->where('votable_id', $voter->getKey())
                ->whereIn('position_id', $byPosition->keys())
                ->pluck('position_id')
                ->unique();

            $now = Carbon::now();
            $rows = [];

            foreach ($byPosition as $positionId => $choices) {
                $position = $positions->get($positionId);
                if (!$position) {
                    $this->fail("Invalid position", "One of the selected positions is not part of this election.", 422);
                }
                if ($alreadyVoted->contains($positionId)) {
                    $this->fail("Vote already cast", "You have already voted for the position {$position->name}.", 409);
                }

                $candidateIds = $choices->pluck('candidate_id')->unique();
                if ($candidateIds->count() !== $choices->count()) {
                    $this->fail("Duplicate choice", "The same candidate was selected more than once for {$position->name}.", 422);
                }
                if ($candidateIds->count() > max(1, $position->seats)) {
                    $this->fail("Too many choices", "You may choose at most {$position->seats} candidate(s) for {$position->name}.", 422);
                }

                foreach ($candidateIds as $candidateId) {
                    $candidate = $candidates->get($candidateId);
                    if (!$candidate || $candidate->election_role_id !== $position->id) {
                        $this->fail("Invalid candidate", "A selected candidate is not standing for {$position->name}.", 422);
                    }
                    if ($candidate->status !== ElectionCandidate::STATUS_ACTIVE) {
                        $this->fail("Candidate unavailable", "A selected candidate for {$position->name} is no longer standing.", 409);
                    }
                    if (!$election->allow_candidate_self_vote
                        && $candidate->candidateable_type === $voter->getMorphClass()
                        && $candidate->candidateable_id === $voter->getKey()) {
                        $this->fail("Self vote not allowed", "Candidates may not vote for themselves in this election.", 403);
                    }

                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'school_branch_id' => $currentSchool->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => $candidate->id,
                        'votable_type' => $voter->getMorphClass(),
                        'votable_id' => $voter->getKey(),
                        'voted_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            ElectionVote::insert($rows);
            return collect($rows);
        });

        $this->broadcast($votes);

        return [
            'election_id' => $electionId,
            'positions_voted' => $votes->pluck('position_id')->unique()->values(),
            'votes_cast' => $votes->count(),
            'voted_at' => $votes->first()['voted_at'] ?? null,
        ];
    }

    /**
     * Positions the voter has already voted for and the candidates chosen.
     */
    public function getVoterBallotStatus(string $electionId, object $currentSchool, Model $voter): array
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        $votes = ElectionVote::where('election_id', $election->id)
            ->where('votable_type', $voter->getMorphClass())
            ->where('votable_id', $voter->getKey())
            ->get(['position_id', 'candidate_id', 'voted_at']);

        return [
            'election_id' => $election->id,
            'can_vote' => $this->eligibility->canVote($election, $voter),
            'voting_open' => $election->isVotingWindowOpen(),
            'votes' => $votes->groupBy('position_id')->map(fn($v) => $v->pluck('candidate_id')->values()),
        ];
    }

    private function broadcast($votes): void
    {
        try {
            $votes->each(function ($vote) {
                $count = ElectionVote::where('candidate_id', $vote['candidate_id'])->count();
                broadcast(new VoteCastEvent($vote['school_branch_id'], $vote['election_id'], $vote['position_id'], $vote['candidate_id'], $count))->toOthers();
            });
        } catch (Throwable $e) {
            // A broadcasting outage must never invalidate a recorded ballot.
            Log::warning('Election vote broadcast failed', ['message' => $e->getMessage()]);
        }
    }
}
