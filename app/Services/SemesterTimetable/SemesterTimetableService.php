<?php

namespace App\Services\SemesterTimetable;

use App\Constant\Constraint\SemesterTimetable\Builder\ConstraintBuilder;
use App\Models\SemesterTimetable\SemesterTimetable;
use App\Models\SemesterTimetable\SemesterTimetableError;

class SemesterTimetableService
{
    public function getTimetableStatus(string $versionId, object $currentSchool): ?string
    {
        $semesterTimetable = SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $semesterTimetable->status;
    }

    public function getTimetableSlots(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable =  SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $semesterTimetable->timetable_slots;
    }

    public function getRequestPayload(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable =  SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $semesterTimetable->request_payload;
    }

    public function getParsedDiagnostics(string $versionId, object $currentSchool): ?array
    {
        $semesterTimetable = SemesterTimetable::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();

        return $semesterTimetable->parsed_diagnostics;
    }

    public function getRawDiagnostics(string $versionId, object $currentSchool): ?array
    {
        $result = [];
        $semesterTimetable = SemesterTimetable::where("timetable_version_id",  $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        $diagnostics = [
            ...$semesterTimetable->raw_diagnostics['hard'],
            ...$semesterTimetable->raw_diagnostics['soft']
        ];
        foreach ($diagnostics as $diagnostic) {
            $result[] = [
                "constraint_key" => $diagnostic["constraint_failed"]['type'],
                "constraint_id" => $diagnostic['constraint_failed']["id"],
                "constraint_title" => ConstraintBuilder::getConstraintTitle($diagnostic["constraint_failed"]['type']),
                "constraint_type" => ConstraintBuilder::getConstraintType($diagnostic["constraint_failed"]['type']),
                ...$diagnostic["constraint_failed"]["details"],
                "blocker_count" => count($diagnostic['blockers'])
            ];
        }
        return $result;
    }

    public function getErrors(string $versionId, object $currentSchool): ?array
    {
        $errors = SemesterTimetableError::where("timetable_version_id", $versionId)
            ->where("school_branch_id", $currentSchool->id)
            ->first();
        return $errors->errors;
    }
}
