<?php

namespace App\Services\Promotion;

use App\Models\Promotion\StudentPromotionRecord;
use App\Models\SchoolBranchSetting;
use App\Models\Specialty;
use App\Models\Student;
use App\Services\RegistrationFee\RegistrationFeeGenerationService;
use App\Services\TuitionFee\TuitionFeeGenerationService;
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

        // What each record allows, including whether there is a next level
        // to promote into, so the app offers only what will be accepted.
        foreach ($promotionRecords as $record) {
            $actions = $this->policy()->describe($record);
            if ($actions['promote']['allowed'] && ($reason = $this->missingNextSpecialty($currentSchool->id, $record))) {
                $actions['promote'] = ['allowed' => false, 'reason' => $reason];
            }
            $record->setAttribute('available_actions', $actions);
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
        return DB::transaction(function () use ($currentSchool, $payload, $actor) {
            $record = $this->lockedRecords($currentSchool, [$payload['record_id']])->first();
            if (!$record) {
                throw $this->recordNotFound();
            }
            $this->decide($currentSchool, $record, 'promote', $actor, $payload['admin_note'] ?? null, throw: true);

            return $record->fresh();
        });
    }

    public function repeatStudent(object $currentSchool, array $payload, object $actor)
    {
        return DB::transaction(function () use ($currentSchool, $payload, $actor) {
            $record = $this->lockedRecords($currentSchool, [$payload['record_id']])->first();
            if (!$record) {
                throw $this->recordNotFound();
            }
            $this->decide($currentSchool, $record, 'repeat', $actor, $payload['admin_note'] ?? null, throw: true);

            return $record->fresh();
        });
    }

    /**
     * Approves the promotion of every selected record that allows it; the
     * others are skipped with the reason. Returns { action, done, skipped }.
     */
    public function bulkPromoteStudents(object $currentSchool, array $payload, object $actor): array
    {
        return $this->bulk($currentSchool, $payload, 'promote', $actor);
    }

    /** Has every selected student that allows it repeat their level. */
    public function bulkRepeatStudents(object $currentSchool, array $payload, object $actor): array
    {
        return $this->bulk($currentSchool, $payload, 'repeat', $actor);
    }

    /**
     * The record's evaluation, one entry per promotion rule:
     *   { record_id, student_name, outcome, status, summary,
     *     counts { passed, failed, needs_review },
     *     rules [{ rule, title, result: passed | failed | needs_review, reasons [string] }] }
     * Failed rules come first, then those needing review, then passed ones.
     */
    public function getStudentPromotionLog(object $currentSchool, string $recordId)
    {
        $record = StudentPromotionRecord::where('school_branch_id', $currentSchool->id)
            ->with('student')
            ->find($recordId);
        $log = $record ? app(PromotionRunLogStore::class)->getStudent($recordId, $currentSchool->id) : null;

        if (!$record || $log === null) {
            throw new AppException(
                "Student promotion log not found.",
                404,
                "No Log Yet",
                "This student has not been evaluated by a promotion run yet, so there is no log to show.",
                null
            );
        }

        return self::presentLog($record, $log);
    }

    public static function presentLog(StudentPromotionRecord $record, array $ruleLogs): array
    {
        $order = ['failed' => 0, 'needs_review' => 1, 'passed' => 2];
        $rules = collect($ruleLogs)
            ->map(function ($rule) {
                $result = !empty($rule['needs_review']) ? 'needs_review' : (!empty($rule['passed']) ? 'passed' : 'failed');
                return [
                    'rule' => $rule['rule'] ?? 'rule',
                    'title' => PromotionRunLogBuilder::ruleTitle($rule['rule'] ?? ''),
                    'result' => $result,
                    'reasons' => collect($rule['reasons'] ?? [])->pluck('reason')->filter()->values()->all(),
                ];
            })
            ->sortBy(fn($rule) => $order[$rule['result']])
            ->values();

        $counts = [
            'passed' => $rules->where('result', 'passed')->count(),
            'failed' => $rules->where('result', 'failed')->count(),
            'needs_review' => $rules->where('result', 'needs_review')->count(),
        ];
        $total = $rules->count();
        $summary = match (true) {
            $total === 0 => 'No promotion rules were evaluated for this student.',
            $counts['failed'] > 0 => sprintf('Did not meet %d of %d promotion rules.', $counts['failed'], $total),
            $counts['needs_review'] > 0 => sprintf('%d of %d rules need an admin to review them.', $counts['needs_review'], $total),
            default => "Met all {$total} promotion rules.",
        };

        return [
            'record_id' => $record->id,
            'student_name' => $record->student?->name,
            'outcome' => $record->outcome,
            'status' => $record->status,
            'summary' => $summary,
            'counts' => $counts,
            'rules' => $rules->all(),
        ];
    }

    private function bulk(object $currentSchool, array $payload, string $action, object $actor): array
    {
        $ids = $payload['record_ids'];
        $note = $payload['admin_note'] ?? null;

        return DB::transaction(function () use ($currentSchool, $ids, $note, $action, $actor) {
            $records = $this->lockedRecords($currentSchool, $ids)->keyBy('id');

            $done = [];
            $skipped = [];
            foreach ($ids as $id) {
                $record = $records->get($id);
                if (!$record) {
                    $skipped[] = ['id' => $id, 'name' => null, 'reason' => 'This record no longer exists.'];
                    continue;
                }
                $name = $record->student?->name;
                if ($reason = $this->decide($currentSchool, $record, $action, $actor, $note, throw: false)) {
                    $skipped[] = ['id' => $id, 'name' => $name, 'reason' => $reason];
                    continue;
                }
                $done[] = ['id' => $id, 'name' => $name, 'outcome' => $record->outcome];
            }

            if (empty($done)) {
                $verb = $action === 'promote' ? 'promoted' : 'set to repeat';
                throw new AppException(
                    "None of the selected students can be {$verb}.",
                    409,
                    $action === 'promote' ? 'Nothing To Promote' : 'Nothing To Repeat',
                    "None of the selected students can be {$verb}. " . ($skipped[0]['reason'] ?? ''),
                    null
                );
            }

            return ['action' => $action, 'done' => $done, 'skipped' => $skipped];
        });
    }

    /**
     * Decides one record. Returns null when done, else why not; with $throw
     * the reason is thrown instead (single endpoints).
     */
    private function decide(object $currentSchool, StudentPromotionRecord $record, string $action, object $actor, ?string $note, bool $throw): ?string
    {
        $refusal = $this->policy()->refusal($record, $action);

        $nextSpecialty = null;
        if (!$refusal && $action === 'promote') {
            if ($reason = $this->missingNextSpecialty($currentSchool->id, $record)) {
                $refusal = ['title' => 'Next Level Specialty Missing', 'description' => $reason, 'code' => 422];
            } else {
                $nextSpecialty = $this->nextSpecialtyFor($currentSchool->id, $record->fromSpecialty ?? $record->student->specialty);
            }
        }

        if ($refusal) {
            if ($throw) {
                throw new AppException($refusal['description'], $refusal['code'], $refusal['title'], $refusal['description'], null);
            }
            return $refusal['description'];
        }

        $record->update([
            'outcome' => $action === 'promote' ? 'promoted' : 'repeated',
            'status' => 'completed',
            'processed_at' => $record->processed_at ?? now(),
            'reviewed_at' => now(),
            'authorable_type' => get_class($actor),
            'authorable_id' => $actor->id,
            'admin_note' => $note,
            'to_specialty_id' => $action === 'promote' ? $nextSpecialty->id : $record->from_specialty_id,
        ]);

        if ($action === 'promote') {
            $record->student->update(['specialty_id' => $nextSpecialty->id]);
            $this->billPromotedStudent($record->student, $nextSpecialty, $this->billsRegistrationFeeOnPromotion($currentSchool->id));
        }

        return null;
    }

    /** Why the record's student has no next level to be promoted into, or null. */
    private function missingNextSpecialty(string $schoolBranchId, StudentPromotionRecord $record): ?string
    {
        $from = $record->fromSpecialty ?? $record->student?->specialty;
        if (!$from) {
            return 'The specialty this student is promoted from could not be found.';
        }

        return $this->nextSpecialtyFor($schoolBranchId, $from)
            ? null
            : "There is no {$from->specialty_name} specialty at the next level. Create it, then approve the promotion.";
    }

    private function lockedRecords(object $currentSchool, array $ids): Collection
    {
        return StudentPromotionRecord::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $ids)
            ->with(['promotionRun', 'student.specialty.level', 'fromSpecialty.level'])
            ->lockForUpdate()
            ->get();
    }

    /** @var array<string, ?Specialty> next specialty by specialty id, per request */
    private array $nextSpecialties = [];
    private ?Collection $levels = null;

    private function nextSpecialtyFor(string $schoolBranchId, Specialty $specialty): ?Specialty
    {
        if (!array_key_exists($specialty->id, $this->nextSpecialties)) {
            $this->levels ??= $this->levelsFor($schoolBranchId);
            $this->nextSpecialties[$specialty->id] = $this->levels->isEmpty() || !$specialty->level
                ? null
                : $this->getNextSpecialty($specialty, $this->levels);
        }

        return $this->nextSpecialties[$specialty->id];
    }

    private function levelsFor(string $schoolBranchId): Collection
    {
        $setting = $this->levelSetting($schoolBranchId);
        return $setting?->value
            ? Educationlevels::whereHas('levelType', fn($query) => $query->where('program_name', $setting->value))->get()
            : collect();
    }

    private function policy(): PromotionRecordDecisionPolicy
    {
        return app(PromotionRecordDecisionPolicy::class);
    }

    private function recordNotFound(): AppException
    {
        return new AppException(
            "Student promotion record not found.",
            404,
            "Record Not Found",
            "The promotion record you are trying to update does not exist or does not belong to this school branch.",
            null
        );
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
     * billed: the new specialty's registration fee when "Charge Registration
     * Fee Upon Promotion" is on, and its tuition for the new specialty's
     * applicable academic year, which becomes the student's academic year.
     * Without one, tuition is deferred until that academic year is created.
     * Records that already exist are left as they are.
     */
    private function billPromotedStudent(Student $student, Specialty $specialty, bool $billRegistrationFee): void
    {
        if ($billRegistrationFee) {
            app(RegistrationFeeGenerationService::class)->generate($student, $specialty);
        }

        app(TuitionFeeGenerationService::class)->syncStudent($student);
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
