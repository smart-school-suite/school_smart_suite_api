<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\OperationalJobs\ElectionVoteStatJob;
use App\Models\Student;
use App\Models\ElectionVotes;
use App\Models\ElectionResults;
use Exception;
use App\Events\VoteCastEvent;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Elections;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoteService
{
    // Implement your logic here

    public function castVote(array $data, $currentSchool)
    {
        try{
             DB::beginTransaction();
             $election = Elections::with('electionType.electionRoles')->find($data['election_id']);

             if (!$election) {
                 return ApiResponseService::error("Election not found", null, 404);
             }

             if($election->voting_status != 'ongoing'){
                return ApiResponseService::error("Voting Period Has Started", null, 404);
             }

             if ($election->is_results_published) {
                 return ApiResponseService::error("Election Results Already Published", null, 400);
             }

             $student = Student::where('school_branch_id', $currentSchool->id)->find($data['student_id']);
             if (!$student) {
                 return ApiResponseService::error("Student not found", null, 404);
             }

             if ($this->hasVotedInSameCategory($currentSchool->id, $data['position_id'], $data['election_id'], $data['student_id'])) {
                 return ApiResponseService::error("You cannot vote multiple times in the same category.", null, 403);
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
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
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
        if($result->vote_count === null){
            $result->vote_count =  1;
        }
        else{
            $result->increment('vote_count');
        }
        return $result->fresh();
    }
}
