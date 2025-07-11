<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Str;

class SchoolService
{


    public function deleteSchool(string $schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $school->delete();
        return $school;
    }

    public function updateSchool(array $data, string $schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        $filterData = array_filter($data);
        $school->update($filterData);
        return $school;
    }

    public function getSchoolDetails($schoolId)
    {
        $school = School::with('schoolbranches')->find($schoolId);
        if (!$school) {
            return ApiResponseService::error("School Not found", null, 404);
        }
        return $school;
    }
}
