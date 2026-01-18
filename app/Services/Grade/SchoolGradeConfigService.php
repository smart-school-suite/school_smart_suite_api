<?php

namespace App\Services\Grade;

use App\Models\SchoolGradesConfig;
use App\Models\Grades;

class SchoolGradeConfigService
{
    public function getSchoolGradeScaleCategories(object $currentSchool)
    {
        $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)
            ->with(['gradesCategory'])->get();
        return $schoolGradesConfig;
    }

    public function getGradeScaleSchoolGradeCategoryId(object $currentSchool, string $schoolGradeScaleCategoryId)
    {
        $schoolGradesConfig = SchoolGradesConfig::findOrFail($schoolGradeScaleCategoryId);
        $grades = Grades::where("school_branch_id", $currentSchool->id)
            ->where("grades_category_id", $schoolGradesConfig->grades_category_id)
            ->with(['lettergrade'])
            ->get();
        return $grades;
    }
}
