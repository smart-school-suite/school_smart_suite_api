<?php

namespace App\Services;

use App\Models\ResitCandidates;

class ResitCandidateService
{
    public function getResitCandidates($currentSchool){
        $resitCandidates = ResitCandidates::
         where("school_branch_id", $currentSchool->id)
         ->with(['resitExam.examtype', 'student.level', 'student.specialty'])
         ->get();
        return $resitCandidates;
    }

    public function deleteCandidates($currentSchool, $candidateId){
        $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
                     ->findOrFail($candidateId);
        $candidate->delete();

    }
}
