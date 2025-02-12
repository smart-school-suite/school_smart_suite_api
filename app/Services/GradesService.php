<?php

namespace App\Services;
use App\Models\Grades;
class GradesService
{
    // Implement your logic here
    public function getGrades($currentSchool){
        $gradesData = Grades::where('school_branch_id', $currentSchool->id)
            ->with(['exam.examtype.semesters', 'lettergrade'])->get();
            return $gradesData;
    }

    public function deleteGrades($currentSchool, $gradeId){
        $gradeExists = Grades::where("school_branch_id", $currentSchool->id)->find($gradeId);
        if(!$gradeExists){
            return ApiResponseService::error("Grade Not Found", null, 404);
        }
        $gradeExists->delete();
        return $gradeExists;
    }


}
