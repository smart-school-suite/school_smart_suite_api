<?php

namespace App\Services\Graduation;

use App\Exceptions\AppException;
use App\Models\Graduation\GraduationRecord;
use App\Models\SchoolBranchSetting;
use App\Models\Specialty;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Models\Promotion\Alumni;
use Illuminate\Support\Facades\DB;

class GraduationRecordService
{
    public function getGraduationRecords(object $currentSchool)
    {
        $graduationRecords = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->with(['batch', 'specialty.level', 'authorable', 'promotionRun', 'student'])
            ->get();

        if ($graduationRecords->isEmpty()) {
            throw new AppException(
                "Graduation Records Not Found",
                404,
                "Resource Not Found",
                "No graduation records found for the specified school branch."
            );
        }

        return $graduationRecords;
    }
    public function getGraduationRecordDetails(object $currentSchool, string $recordId)
    {
        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->with(['batch', 'specialty', 'authorable', 'promotionRun', 'student'])
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        return $graduationRecord;
    }
    public function blockGraduation(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->clearance_status === "blocked") {
            throw new AppException(
                "Graduation Record Already Blocked",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already blocked."
            );
        }

        $updateData = [
            "clearance_status" => "blocked",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function unBlockGraduation(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->clearance_status === "cleared") {
            throw new AppException(
                "Graduation Record Already Cleared",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already cleared."
            );
        }

        $updateData = [
            "clearance_status" => "cleared",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function approveEligibility(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->eligibility_status === "eligible") {
            throw new AppException(
                "Graduation Record Already Eligible",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already approved as eligible."
            );
        }

        $updateData = [
            "eligibility_status" => "eligible",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function markInEligible(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->eligibility_status === "ineligible") {
            throw new AppException(
                "Graduation Record Already Ineligible",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already marked as ineligible."
            );
        }

        $updateData = [
            "eligibility_status" => "ineligible",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function requestReview(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->eligibility_status === "pending_review") {
            throw new AppException(
                "Graduation Record Already Pending Review",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already pending review."
            );
        }

        $updateData = [
            "eligibility_status" => "pending_review",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function approveContinuation(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
            ->where("id", $recordId)
            ->with(['specialty'])
            ->first();

        if (!$graduationRecord) {
            throw new AppException(
                "Graduation Record Not Found",
                404,
                "Resource Not Found",
                "Graduation record not found."
            );
        }

        if ($graduationRecord->status === "continuing") {
            throw new AppException(
                "Graduation Record Already Continuing",
                400,
                "Action Cannot Be Processed",
                "This graduation record is already approved for continuation."
            );
        }

        $levelSettings = $this->levelSetting($currentSchool->id);
        $levels = Educationlevels::whereHas(
            "levelType",
            fn($query) =>
            $query->where("program_name", $levelSettings->value)
        )->get();

        $specialty = $graduationRecord->specialty;
        $nextSpecialty = $this->getNextSpecialty($specialty, $levels);

        if (!$nextSpecialty) {
            throw new AppException(
                "Next Specialty Not Found",
                400,
                "Action Cannot Be Processed",
                "Unable to determine the next specialty for continuation."
            );
        }

        $updateData = [
            "status" => "continuing",
        ];

        if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
            $updateData["admin_note"] = $payload["admin_note"];
        }

        $graduationRecord->update($updateData);

        return $graduationRecord;
    }
    public function confirmAlumni(object $currentSchool, array $payload)
    {
        $recordId = $payload["record_id"];

        return DB::transaction(function () use ($currentSchool, $recordId, $payload) {
            $graduationRecord = GraduationRecord::where("school_branch_id", $currentSchool->id)
                ->where("id", $recordId)
                ->lockForUpdate()
                ->first();

            if (!$graduationRecord) {
                throw new AppException(
                    "Graduation Record Not Found",
                    404,
                    "Resource Not Found",
                    "Graduation record not found."
                );
            }

            if ($graduationRecord->status === "alumni") {
                throw new AppException(
                    "Graduation Record Already Confirmed as Alumni",
                    400,
                    "Action Cannot Be Processed",
                    "This graduation record is already confirmed as alumni."
                );
            }

            $updateData = [
                "status" => "alumni",
            ];

            $alumniAttributes = [];

            if (array_key_exists("admin_note", $payload) && !is_null($payload["admin_note"])) {
                $updateData["admin_note"] = $payload["admin_note"];
                $alumniAttributes["admin_note"] = $payload["admin_note"];
            }

            $graduationRecord->update($updateData);

            Alumni::firstOrCreate(
                [
                    'record_id' => $recordId,
                    'school_branch_id' => $currentSchool->id,
                ],
                $alumniAttributes
            );

            return $graduationRecord;
        });
    }
    private function levelSetting(string $schoolBranchId)
    {
        return  SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->where("key", "level.start");
            })->first();
    }
    private function getNextSpecialty(Specialty $specialty, Collection $levels): ?Specialty
    {
        $currentLevel = (int) $specialty->level->level;

        $nextLevelNumber = $levels
            ->pluck('level')
            ->map(fn($level) => (int) $level)
            ->filter(fn($level) => $level > $currentLevel)
            ->sort()
            ->first();

        if ($nextLevelNumber === null) {
            return null;
        }

        $nextEducationLevel = $levels->first(
            fn($level) => (int) $level->level === $nextLevelNumber
        );

        return Specialty::where("school_branch_id", $specialty->school_branch_id)
            ->where("specialty_name", $specialty->specialty_name)
            ->where("level_id", $nextEducationLevel->id)
            ->first();
    }
}
