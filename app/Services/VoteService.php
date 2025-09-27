<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\OperationalJobs\ElectionVoteStatJob;
use App\Models\Student;
use App\Models\ElectionVotes;
use App\Models\ElectionResults;
use Exception;
use App\Events\VoteCastEvent;
use App\Exceptions\AppException;
use App\Models\Elections;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoteService
{
    public function castVote(array $data, $currentSchool)
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

            $student = Student::where('school_branch_id', $currentSchool->id)->find($data['student_id']);
            if (!$student) {
                throw new AppException(
                    "The student attempting to vote was not found.",
                    404,
                    "Student Not Found",
                    "The student ID provided does not match any student in the system.",
                    null
                );
            }

            if ($this->hasVotedInSameCategory($currentSchool->id, $data['position_id'], $data['election_id'], $data['student_id'])) {
                throw new AppException(
                    "This student has already voted for this position in this election.",
                    409,
                    "Vote Already Cast",
                    "Each student is only allowed to vote once per position in an election.",
                    null
                );
            }

            $voteId = Str::uuid();
            ElectionVotes::create([
                "id" => $voteId,
                "school_branch_id" => $currentSchool->id,
                "election_id" => $data['election_id'],
                "candidate_id" => $data['candidate_id'],
                "student_id" => $data['student_id'],
                "position_id" => $data['position_id'],
                "voted_at" => now(),
            ]);

            $updatedResult = $this->updateElectionResults($currentSchool->id, $data);
            broadcast(new VoteCastEvent($updatedResult))->toOthers();

            DB::commit();
            ElectionVoteStatJob::dispatch($voteId, $currentSchool->id);

            return $updatedResult;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected error occurred while casting the vote.",
                500,
                "Voting Error",
                "A server-side issue prevented the vote from being cast successfully.",
                null
            );
        }
    }

    private function hasVotedInSameCategory($schoolBranchId, $positionId, $electionId, $studentId)
    {
        return ElectionVotes::where('school_branch_id', $schoolBranchId)
            ->where('position_id', $positionId)
            ->where('election_id', $electionId)
            ->where('student_id', $studentId)
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
