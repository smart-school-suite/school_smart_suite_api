<?php

namespace App\Services\Promotion;

use App\Models\Promotion\StudentPromotionRecord;
use App\Models\SchoolBranchSetting;
use App\Models\Promotion\StudentPromotionLog;
use App\Models\Specialty;
use App\Models\RegistrationFee;
use App\Models\Student;
use App\Models\TuitionFees;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;

class StudentPromotionRecordService
{
    public function getStudentPromotionRecords(object $currentSchool)
    {
        $promotionRecords = StudentPromotionRecord::where("school_branch_id", $currentSchool->id)
            ->with([
                'promotionRun.schoolYear.systemAcademicYear',
                'student',
                'fromSpecialty.level',
                'toSpecialty.level',
            ])
            ->latest()
            ->get();

        if ($promotionRecords->isEmpty()) {
            throw new AppException(
                "No student promotion records found.",
                404,
                "Records Not Found",
                "There are currently no student promotion records available for this school branch.",
                null
            );
        }

        return $promotionRecords;
    }
    public function getStudentPromotionRecordDetail(object $currentSchool, string $recordId)
    {
        $record = StudentPromotionRecord::where("school_branch_id", $currentSchool->id)
            ->with([
                'promotionRun',
                'student',
                'fromSpecialty',
                'toSpecialty',
                'schoolYear'
            ])
            ->where("id", $recordId)
            ->first();

        if (!$record) {
            throw new AppException(
                "Student promotion record not found.",
                404,
                "Record Not Found",
                "The requested student promotion record could not be found or does not belong to the current school branch.",
                null
            );
        }

        return $record;
    }
    public function promoteStudent(object $currentSchool, array $payload, object $actor)
    {
        $recordId = $payload['record_id'];
        $promotionRecord = StudentPromotionRecord::where("school_branch_id", $currentSchool->id)
            ->with(['promotionRun.schoolYear', 'student.specialty', 'fromSpecialty', 'toSpecialty'])
            ->where("id", $recordId)
            ->first();

        if (!$promotionRecord) {
            throw new AppException(
                "Student promotion record not found.",
                404,
                "Record Not Found",
                "The promotion record you are trying to update does not exist or does not belong to this school branch.",
                null
            );
        }

        if ($promotionRecord->status === 'completed') {
            throw new AppException(
                "Promotion record has already been processed.",
                400,
                "Already Completed",
                "This student's promotion decision has already been finalized and completed.",
                null
            );
        }

        $student = $promotionRecord->student;

        if (!$student) {
            throw new AppException(
                "Associated student record not found.",
                404,
                "Student Not Found",
                "The student associated with this promotion record could not be found.",
                null
            );
        }

        $levelSettings = $this->levelSetting($currentSchool->id);

        $levels = Educationlevels::whereHas(
            "levelType",
            fn($query) =>
            $query->where("program_name", $levelSettings->value)
        )->get();


        $nextSpecialty = $this->getNextSpecialty($student->specialty, $levels);

        if (!$nextSpecialty) {
            throw new AppException(
                "Next specialty could not be resolved.",
                422,
                "Specialty Unresolved",
                "Unable to determine the next specialty progression level for this student.",
                null
            );
        }

        $billRegistrationFee = $this->billsRegistrationFeeOnPromotion($currentSchool->id);

        return DB::transaction(function () use ($promotionRecord, $student, $nextSpecialty, $payload, $actor, $billRegistrationFee) {
            $promotionRecord->update([
                'outcome' => 'promoted',
                'status' => 'completed',
                'processed_at' => now(),
                'reviewed_at' => now(),
                'authorable_type' => get_class($actor),
                'authorable_id' => $actor->id,
                'admin_note' => $payload['admin_note'] ?? null,
                'to_specialty_id' => $nextSpecialty->id,
            ]);

            $student->update([
                'specialty_id' => $nextSpecialty->id,
            ]);

            $this->billPromotedStudent($student, $nextSpecialty, $billRegistrationFee);

            return $promotionRecord->fresh();
        });
    }
    public function repeatStudent(object $currentSchool, array $payload, object $actor)
    {
        $recordId = $payload['record_id'];

        $promotionRecord = StudentPromotionRecord::where("school_branch_id", $currentSchool->id)
            ->with(['promotionRun', 'student', 'fromSpecialty', 'toSpecialty', 'schoolYear'])
            ->where("id", $recordId)
            ->first();

        if (!$promotionRecord) {
            throw new AppException(
                "Student promotion record not found.",
                404,
                "Record Not Found",
                "The promotion record you are trying to update does not exist or does not belong to this school branch.",
                null
            );
        }

        if ($promotionRecord->status === 'completed') {
            throw new AppException(
                "Promotion record has already been processed.",
                400,
                "Already Completed",
                "This student's promotion decision has already been finalized and completed.",
                null
            );
        }

        $student = $promotionRecord->student;

        if (!$student) {
            throw new AppException(
                "Associated student record not found.",
                404,
                "Student Not Found",
                "The student associated with this promotion record could not be found.",
                null
            );
        }

        return DB::transaction(function () use ($promotionRecord, $student, $payload, $actor) {
            $promotionRecord->update([
                'outcome' => 'repeated',
                'status' => 'completed',
                'processed_at' => now(),
                'reviewed_at' => now(),
                'authorable_type' => get_class($actor),
                'authorable_id' => $actor->id,
                'admin_note' => $payload['admin_note'] ?? null,
                'to_specialty_id' => $promotionRecord->from_specialty_id,
            ]);

            return $promotionRecord->fresh();
        });
    }
    public function getStudentPromotionLog(object $currentSchool, string $recordId)
    {
        $log = StudentPromotionLog::where("school_branch_id", $currentSchool->id)
            ->where("record_id", $recordId)
            ->first();

        if (!$log) {
            throw new AppException(
                "Student promotion log not found.",
                404,
                "Log Not Found",
                "The requested promotion log could not be found or does not belong to the current school branch.",
                null
            );
        }

        return $log;
    }
    public function getNextPromotionSpecialty(object $currentSchool, string $specialtyId)
    {
        $levelSettings = $this->levelSetting($currentSchool->id);
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->where("id", $specialtyId)
            ->first();

        $levels = Educationlevels::whereHas(
            "levelType",
            fn($query) =>
            $query->where("program_name", $levelSettings->value)
        )->get();
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
            ->where("department_id", $specialty->department_id)
            ->with(['level'])
            ->where("level_id", $nextEducationLevel->id)
            ->first();
    }
    /**
     * Bills a promoted student for their new level, as a new student is
     * billed: the new specialty's tuition always, and its registration fee
     * when "Charge Registration Fee Upon Promotion" is on. Records that
     * already exist for that specialty are left as they are.
     */
    private function billPromotedStudent(Student $student, Specialty $specialty, bool $billRegistrationFee): void
    {
        $existing = fn(string $model) => $model::where('school_branch_id', $student->school_branch_id)
            ->where('student_id', $student->id)
            ->where('specialty_id', $specialty->id)
            ->exists();

        if ($billRegistrationFee && !$existing(RegistrationFee::class)) {
            RegistrationFee::create([
                'specialty_id' => $specialty->id,
                'school_branch_id' => $student->school_branch_id,
                'amount' => $specialty->registration_fee,
                'status' => 'not paid',
                'student_id' => $student->id,
            ]);
        }

        if (!$existing(TuitionFees::class)) {
            TuitionFees::create([
                'specialty_id' => $specialty->id,
                'amount_paid' => 0.00,
                'school_branch_id' => $student->school_branch_id,
                'tution_fee_total' => $specialty->school_fee,
                'student_id' => $student->id,
            ]);
        }
    }

    private function billsRegistrationFeeOnPromotion(string $schoolBranchId): bool
    {
        $value = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', fn($query) => $query->where('key', 'promotion.bill_student_registration_fee'))
            ->first()?->value;

        return $value === null ? true : filter_var($value, FILTER_VALIDATE_BOOLEAN);
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
            ->where("department_id", $specialty->department_id)
            ->where("level_id", $nextEducationLevel->id)
            ->first();
    }
}
