<?php

namespace App\Services\Election;

use App\Models\ElectionVotes;
use App\Models\ElectionResults;
use Exception;
use App\Events\VoteCastEvent;
use App\Exceptions\AppException;
use App\Models\Elections;
use App\Models\VoterStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Events\Analytics\ElectionAnalyticsEvent;
use App\Constant\Analytics\Election\ElectionAnalyticsEvent as ElectionEvent;

class ElectionVoteService
{
    public function castVote(array $data, $currentSchool, $authUser)
    {
        try {
            DB::beginTransaction();

            $election = Elections::with('electionType.electionRoles')->find($data['election_id']);
            if (!$election) {
                throw new AppException(
                    "The specified election was not found.",
                    404,
                    "Election Not Found",
                    "The election you are trying to vote in does not exist.",
                    null
                );
            }

            if ($election->voting_status !== 'ongoing') {
                throw new AppException(
                    "Voting is not currently open for this election.",
                    403,
                    "Voting Not Allowed",
                    "The voting period for this election has either not started or has already ended.",
                    null
                );
            }

            if ($election->is_results_published) {
                throw new AppException(
                    "The election results have already been published.",
                    403,
                    "Results Published",
                    "You cannot cast a vote after the election results have been made public.",
                    null
                );
            }


            if ($this->hasVotedInSameCategory($currentSchool->id, $data['position_id'], $data['election_id'], $authUser)) {
                throw new AppException(
                    "This User has already voted for this position in this election.",
                    409,
                    "Vote Already Cast",
                    "Each User is only allowed to vote once per position in an election.",
                    null
                );
            }

            $voteId = Str::uuid();
            ElectionVotes::create([
                "id" => $voteId,
                "school_branch_id" => $currentSchool->id,
                "election_id" => $data['election_id'],
                "candidate_id" => $data['candidate_id'],
                "votable_id" => $authUser['userId'],
                "votable_type" => $authUser["userType"],
                "position_id" => $data['position_id'],
                "voted_at" => now(),
            ]);

            VoterStatus::create([
                "school_branch_id" => $currentSchool->id,
                "election_id" => $data['election_id'],
                "candidate_id" => $data['candidate_id'],
                "votable_id" => $authUser['userId'],
                "votable_type" => $authUser["userType"],
                "position_id" => $data['position_id'],
                "status" => true
            ]);

            $updatedResult = $this->updateElectionResults($currentSchool->id, $data);
            broadcast(new VoteCastEvent($updatedResult))->toOthers();

            DB::commit();
            event(new ElectionAnalyticsEvent(
                eventType: ElectionEvent::VOTE_CASTED,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "election_id" => $data['election_id'],
                    "candidate_id" => $data['candidate_id'],
                    "election_role_id" => $data["position_id"],
                    "election_type_id" => $election->election_type_id
                ]
            ));
            return $updatedResult;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Voting Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AppException(
                "An unexpected error occurred while casting the vote.",
                500,
                "Voting Error",
                "A server-side issue prevented the vote from being cast successfully.",
                null
            );
        }
    }

    private function hasVotedInSameCategory($schoolBranchId, $positionId, $electionId, $authUser)
    {
        return VoterStatus::where('school_branch_id', $schoolBranchId)
            ->where('position_id', $positionId)
            ->where('election_id', $electionId)
            ->where('votable_id', $authUser['userId'])
            ->exists();
    }

    private function updateElectionResults($schoolBranchId, $validatedData)
    {
        $result = ElectionResults::where("school_branch_id", $schoolBranchId)
            ->where("election_id", $validatedData['election_id'])
            ->where("position_id", $validatedData['position_id'])
            ->where("candidate_id", $validatedData['candidate_id'])
            ->first();
        if ($result->vote_count === null) {
            $result->vote_count =  1;
        } else {
            $result->increment('vote_count');
        }
        return $result->fresh();
    }
}
