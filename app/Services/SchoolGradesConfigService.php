<?php

namespace App\Services;

use App\Models\SchoolGradesConfig;

class SchoolGradesConfigService
{
    // Implement your logic here

    public function getSchoolGradeConfig($currentSchool){
      $schoolGradesConfig = SchoolGradesConfig::where("school_branch_id", $currentSchool->id)->with(['gradesCategory'])->get();
      return $schoolGradesConfig;
    }

}
