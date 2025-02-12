<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ElectionVotes;
use App\Models\ElectionResults;
use Illuminate\Support\Carbon;
use App\Models\Elections;

class VoteService
{
    // Implement your logic here

    public function castVote(array $data, $currentSchool)
    {
        $election = Elections::with(['electionRole'])->find($data['election_id']);
        if (!$election) {
            return ApiResponseService::error("Election not found", null, 404);
        }
        if ($election->is_results_published) {
            return ApiResponseService::error("Election Results Already Published", null, 404);
        }
        $student = Student::where('school_branch_id', $currentSchool->id)
            ->find($data['student_id']);
        if (!$student) {
            return ApiResponseService::error("Student Not found", null, 404);
        }
        if ($this->hasVotedInSameCategory($currentSchool->id, $data['position_id'], $data['election_id'], $data['student_id'])) {
            return ApiResponseService::error("You Cannot vote multiple times In thesame Category", null, 403);
        }
        $electionVotes = ElectionVotes::create([
            "school_branch_id" => $currentSchool->id,
            "election_id" => $data['election_id'],
            "candidate_id" => $data['candidate_id'],
            "student_id" => $data['student_id'],
            "position_id" => $data['position_id'],
            "voted_at" => Carbon::now(),
        ]);

        $this->updateElectionResults($currentSchool->id, $data);

        return $electionVotes;
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
        $electionResult = ElectionResults::where("school_branch_id", $schoolBranchId)
            ->where("election_id", $validatedData['election_id'])
            ->where("position_id", $validatedData['position_id'])
            ->where("candidate_id", $validatedData['candidate_id'])
            ->first();
        if (!$electionResult) {
            ElectionResults::create([
                'vote_count' => 1,
                'election_id' => $validatedData['election_id'],
                'position_id' => $validatedData['position_id'],
                'candidate_id' => $validatedData['candidate_id'],
                'school_branch_id' => $schoolBranchId
            ]);
        } else {
            $electionResult->increment('vote_count');
        }
    }
}
