<?php

namespace App\Services;

use App\Models\AccessedResitStudent;

class AccessedStudentResitService
{
    // Implement your logic here
    public function getAccessedResitStudent($currentSchool)
    {
        $accessedResitStudents = AccessedResitStudent::where("school_branch_id", $currentSchool->id)
            ->with(['student', 'exam.examType', 'exam.specialty', 'exam.level'])
            ->get();
        return $accessedResitStudents;
    }

    public function deleteAccessedResitStudent($currentSchool, $accessmentId)
    {
        $candidate = AccessedResitStudent::findOrFail($accessmentId);
        $candidate->delete();
        return $candidate;
    }
}
