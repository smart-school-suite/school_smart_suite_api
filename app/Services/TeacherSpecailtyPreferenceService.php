<?php

namespace App\Services;

use App\Models\TeacherSpecailtyPreference;

class TeacherSpecailtyPreferenceService
{
    // Implement your logic here

    public function getTeacherPreference($teacherId, $currentSchool)
    {
        $teacherSpecailtyPreference = TeacherSpecailtyPreference::where("school_branch_id",  $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->with(['teacher', 'specailty'])
            ->get();
        return $teacherSpecailtyPreference;
    }
}
