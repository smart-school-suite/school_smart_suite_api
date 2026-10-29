<?php

namespace App\Services\School;

use App\Exceptions\AppException;
use App\Models\School;

class SchoolService
{
    public function deleteSchool(string $schoolId, object $currentSchool, $authAdmin): School
    {
        $school = School::find($schoolId);

        if (!$school) {
            throw new AppException(
                "School not found with ID {$schoolId}.",
                404,
                "School Not Found",
                "We couldn't find the school you're trying to delete. Please check and try again.",
                null
            );
        }

        $school->delete();

        return $school;
    }

    public function updateSchool(array $data, string $schoolId, $currentSchool, $authAdmin): School
    {
        $school = School::find($schoolId);

        if (!$school) {
            throw new AppException(
                "School not found with ID {$schoolId}.",
                404,
                "School Not Found",
                "We couldn't find the school you're trying to update. Please check and try again.",
                null
            );
        }

        $filterData = array_filter($data);
        $school->update($filterData);

        return $school;
    }

    public function getSchoolDetails(string $schoolId): School
    {
        $school = School::with(['schoolbranches', 'currentLogo'])->find($schoolId);

        if (!$school) {
            throw new AppException(
                "School not found with ID {$schoolId}.",
                404,
                "School Not Found",
                "We couldn't find the school details you requested. Please try again.",
                null
            );
        }

        return $school;
    }
}
