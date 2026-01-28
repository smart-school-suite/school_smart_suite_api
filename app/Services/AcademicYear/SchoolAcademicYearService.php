<?php

namespace App\Services\AcademicYear;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;

class SchoolAcademicYearService
{
    public function createSchoolAcademicYear(array $data, object $currentSchool)
    {
        $existingAcademicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->where('school_year_id', $data['school_year_id'])
            ->where('specialty_id', $data['specialty_id'])
            ->with(['systemAcademicYear', 'specialty.level'])
            ->first();

        if ($existingAcademicYear) {
            throw new AppException(
                'An academic year with the same school year and specialty already exists for this school branch.',
                400,
                "Existing Academic Year",
                "Academic Year {$existingAcademicYear->systemAcademicYear->name} for {$existingAcademicYear->specialty->specialty_name}, {$existingAcademicYear->specialty->level->level} already exists."
            );
        }

        $academicYear = SchoolAcademicYear::create([
            'specialty_id' => $data['specialty_id'],
            'school_branch_id' => $currentSchool->id,
            'school_year_id' => $data['school_year_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
        ]);

        return $academicYear;
    }

    public function updateSchoolAcademicYear(object $currentSchool, string $schoolAcademicYearId, array $data)
    {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with('systemAcademicYear')
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found.',
                404,
                "Not Found",
                "The school academic year does not exist or has been deleted."
            );
        }

        if (
            isset($data['school_year_id']) &&
            $data['school_year_id'] !== $academicYear->school_year_id &&
            $academicYear->start_date?->isPast()
        ) { 

            throw new AppException(
                'Cannot change the academic year reference after the period has started.',
                422,
                "Unprocessable Entity",
                "The reference academic year (system year) cannot be modified once the school's academic period has begun."
            );
        }

        $filteredData = array_filter($data);
        $academicYear->update($filteredData);

        return $academicYear->fresh(['systemAcademicYear']);
    }

    public function deleteSchoolAcademicYear(object $currentSchool, string $schoolAcademicYearId)
    {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found.',
                404,
                "Not Found",
                "The school academic year does not exist or has been deleted."
            );
        }

        $academicYear->delete();

        return true;
    }
    public function getSchoolAcademicYears(object $currentSchool)
    {
        $schoolAcademicYears = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty'])
            ->orderBy('start_date', 'desc')
            ->get();
        if ($schoolAcademicYears->isEmpty()) {
            throw new AppException(
                'No School Academic Years found for the given school branch.',
                404,
                "School Academic Years Not Found",
                "Not School Academic Year Found For this School Branch Please Create An Academic Year to Continue"
            );
        }

        return $schoolAcademicYears;
    }

    public function getSchoolAcademicYearById(object $currentSchool, string $schoolAcademicYearId)
    {
        $academicYear = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->with(['systemAcademicYear', 'specialty.level'])
            ->find($schoolAcademicYearId);

        if (!$academicYear) {
            throw new AppException(
                'School Academic Year not found for the given school branch.',
                404,
                "Not Found",
                "School Academic Year Not Found For this School Branch, Ensure that it has not been deleted and try again"
            );
        }

        return $academicYear;
    }
}
