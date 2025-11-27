<?php

namespace App\Services\Grade;
use App\Models\SchoolGradesConfig;
use App\Models\Grades;
class SchoolGradeConfigService
{
        public function getSchoolGradeConfig($currentSchool)
    {
        $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->with(['gradesCategory'])->get();
        return $schoolGradesConfig;
    }

    public function getGradingBySchoolGradeCongfig(object $currentSchool, string $schoolGradeConfigId)
    {
        $schoolGradesConfig = SchoolGradesConfig::findOrFail($schoolGradeConfigId);
        $grades = Grades::where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
            ->with(['lettergrade'])
            ->get();
        return $grades;
    }
}
