<?php

namespace App\Services;

use App\Models\SchoolSemester;
use App\Models\TeacherSpecailtyPreference;
use App\Models\Timetable;

class AIGenTimetableService
{
    public function generateTimetable($currentSchool, $schoolSemesterId){
         $schoolSemester = SchoolSemester::where("school_branch_id", $currentSchool->id)
                                           ->findOrFail($schoolSemesterId);
        $teacher = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
                                              ->where("specialty_id", $schoolSemester->specialty_id)
                                              ->with("teacher")->get();
        $teacherBusySlots = Timetable::where("school_branch_id", $currentSchool->id)
                                       ->whereIn("teacher_id", $teacher->pluck("teacher_id"))
                                       ->get();
        return [
             'school_semester' => $schoolSemester,
             'teachers' => $teacher,
             'teacher_busy_slots' => $teacherBusySlots
        ];
    }
}
