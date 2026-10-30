<?php

namespace App\Services\ResitExam;

use App\Models\ResitCandidates;
use App\Exceptions\AppException;

class ResitCandidateService
{
    public function getResitCandidates(object $currentSchool)
    {
        $resitCandidates = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->with([
                'resitExam.examType',
                'resitExam.schoolYear.specialty',
                'resitExam.schoolYear.systemAcademicYear',
                'student',
                'resitScores'
            ])
            ->get();
        return $resitCandidates;
    }

    public function deleteCandidates(object $currentSchool, string $candidateId, object $authAdmin)
    {
        $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->find($candidateId);
        if ($candidate === null) {
            throw new AppException(
                "Resit candidate not found.",
                404,
                "Candidate Not Found",
                "The resit candidate you are trying to delete does not exist.",
                "/resit-candidates"
            );
        }
        $candidate->delete();
        return $candidate;
    }
}
