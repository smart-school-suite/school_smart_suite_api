<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Support\Str;

class SchoolService
{
    // Implement your logic here
    public function createSchool(array $data)
    {
        $new_school_instance = new School();
        $random_id = Str::uuid()->toString();
        $new_school_instance->id = $random_id;
        $new_school_instance->country_id = $data["country_id"];
        $new_school_instance->name = $data["name"];
        $new_school_instance->address = $data["address"];
        $new_school_instance->city = $data["city"];
        $new_school_instance->state = $data["state"];
        $new_school_instance->motor = $data["motor"];
        $new_school_instance->type = $data["type"];
        $new_school_instance->established_year = $data["established_year"];
        $new_school_instance->director_name = $data["director_name"];
        $new_school_instance->save();
        return $random_id;
    }

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
