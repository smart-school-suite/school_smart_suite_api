<?php

namespace App\Services\Resit;

use App\Models\ResitCandidates;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;

class ResitCandidateService
{
    public function getResitCandidates($currentSchool)
    {
        $resitCandidates = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->with(['resitExam.examtype', 'student.level', 'student.specialty'])
            ->get();
        if ($resitCandidates->isEmpty()) {
            throw new AppException(
                "No resit candidates found for this school branch.",
                404,
                "No Candidates Found",
                "There are no resit candidates available. Candidates are automatically created when you create a resit exam.",
                "/resit-exams"
            );
        }
        return $resitCandidates;
    }

    public function deleteCandidates($currentSchool, $candidateId, $authAdmin)
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
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.resitCandidate.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "resitExamCandidateManagement",
                "authAdmin" => $authAdmin,
                "data" => $candidate,
                "message" => "Resit Exam Candidate Deleted",
            ]
        );
        return $candidate;
    }
}
