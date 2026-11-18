<?php

namespace App\Jobs\Promotion;

use App\Models\AdditionalFees;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolBranchSetting;
use App\Models\Studentresit;
use App\Models\TuitionFees;
use App\Models\Educationlevels;
use App\Models\Specialty;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Promotion\StudentPromotionRecord;
use App\Models\Promotion\PromotionRunLog;
use App\Services\PromotionRules\AdditionalFeeRuleService;
use App\Services\PromotionRules\EvaluationRuleService;
use App\Services\PromotionRules\ExamScoreRuleService;
use App\Services\PromotionRules\ResitRuleService;
use App\Services\PromotionRules\TuitionFeeRuleService;
use App\Services\Promotion\PromotionRunActionPolicy;
use App\Services\Promotion\PromotionRunLogBuilder;
use App\Services\Promotion\PromotionRunLogStore;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class StudentPromotionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    private const CANCEL_CHECK_EVERY = 20;
    public $timeout = 300;
    protected string $schoolBranchId;
    protected string $runId;
    public function __construct(string $runId, string $schoolBranchId)
    {
        $this->runId = $runId;
        $this->schoolBranchId = $schoolBranchId;
    }
    public function handle(): void
    {
        $schoolBranchId = $this->schoolBranchId;
        $runId = $this->runId;

        $promotionRun = PromotionRun::where("school_branch_id", $schoolBranchId)
            ->where("id", $runId)
            ->with(['schoolYear.specialty.student', 'schoolYear.specialty.level', 'schoolYear.systemAcademicYear'])
            ->first();

        // Nothing to log against: the run no longer exists.
        if (!$promotionRun) {
            return;
        }

        // Only a run that is still scheduled, queued or running is processed:
        // a run cancelled (or finished) after this job was queued is left alone.
        if (!in_array($promotionRun->status, PromotionRunActionPolicy::IN_PROGRESS, true)) {
            return;
        }
        $promotionRun->update(['status' => 'processing']);

        $log = new PromotionRunLogBuilder();
        [$specialty, $students, $nextSpecialty] = $this->checkCanRun($promotionRun, $log);

        if ($log->hasErrors()) {
            $promotionRun->update(['status' => 'failed', 'completed_at' => now()]);
            $log->save($promotionRun, 'failed', 'Promotion run could not start', [
                'total' => $students->count(), 'processed' => 0, 'promoted' => 0, 'retained' => 0, 'needs_review' => 0,
            ]);
            return;
        }

        $studentIds = $students->pluck('id');

        $examTypes = Examtype::with(['semesters' => function ($query) {
            $query->where("count", "<", 3);
        }])->where("type", "!=", "resit")->get();

        $studentMarks = ExamScore::where("school_branch_id", $schoolBranchId)
            ->whereHas('candidate', fn($q) => $q->whereIn("student_id", $studentIds))
            ->with(['exam.examType.semesters', 'candidate.student', 'course', 'exam.examType', 'grade'])
            ->get();

        $studentAdditionalFees = AdditionalFees::where("school_branch_id", $schoolBranchId)
            ->whereIn("student_id", $studentIds)
            ->with(['feeCategory', 'student', 'additionalFeeTranctions'])
            ->get();

        $studentTuitionFees = TuitionFees::where("school_branch_id", $schoolBranchId)
            ->whereIn("student_id", $studentIds)
            ->with(['student', 'tuitionFeeTransactions'])
            ->get();

        $studentResits = Studentresit::where("school_branch_id", $schoolBranchId)
            ->whereIn("student_id", $studentIds)
            ->with(['student', 'courses'])
            ->get();

        $promotionSetting = $this->getPromotionSetting($schoolBranchId);

        $tuitionFeeRule = app(TuitionFeeRuleService::class);
        $additionalFeeRule = app(AdditionalFeeRuleService::class);
        $examScoreRule = app(ExamScoreRuleService::class);
        $resitRule = app(ResitRuleService::class);
        $evaluationRule = app(EvaluationRuleService::class);

        $passedCount = 0;
        $failedCount = 0;
        $needsReviewCount = 0;

        $processed = 0;
        foreach ($students as $student) {
            // Stop between students when the run is cancelled while running.
            if ($processed > 0 && $processed % self::CANCEL_CHECK_EVERY === 0 && $this->wasCancelled($promotionRun)) {
                $this->stopCancelled($promotionRun, $log, $students->count(), $processed, $passedCount, $failedCount, $needsReviewCount);
                return;
            }
            $processed++;

            $scores = $studentMarks->filter(fn($marks) => $marks->candidate?->student_id === $student->id);
            $resits = $studentResits->where("student_id", $student->id);
            $tuitionFees = $studentTuitionFees->where("student_id", $student->id);
            $additionalFees = $studentAdditionalFees->where("student_id", $student->id);

            $rules = collect([
                $additionalFeeRule->additionalFeeRule($additionalFees, $promotionSetting),
                $tuitionFeeRule->tuitionFeeRule($tuitionFees, $promotionSetting),
                $resitRule->carryOverRule($resits, $promotionSetting),
                $evaluationRule->gpaRule($scores, $promotionSetting, $examTypes),
                $examScoreRule->incompleteExamScoresRule($scores, $examTypes),
            ]);

            $needsReview = $rules->contains(fn($rule) => $rule->needsReview === true);
            $overallPassed = $rules->every(fn($rule) => $rule->passed === true);

            if ($needsReview) {
                $needsReviewCount++;
                $outcome = 'pending_review';
                $status = 'pending';
                $toSpecialtyId = $nextSpecialty->id;
            } elseif ($overallPassed) {
                $passedCount++;
                $outcome = 'promoted';
                $status = 'completed';
                $toSpecialtyId = $nextSpecialty->id;
            } else {
                $failedCount++;
                $outcome = 'retained';
                $status = 'completed';
                $toSpecialtyId = $specialty->id;
            }

            $log->student($student->id, $student->name, $outcome, $rules);

            $studentPromotionRecord = StudentPromotionRecord::updateOrCreate(
                [
                    'promotion_run_id' => $runId,
                    'student_id'       => $student->id,
                    'school_branch_id' => $schoolBranchId,
                ],
                [
                    'outcome'           => $outcome,
                    'status'            => $status,
                    'processed_at'      => now(),
                    'from_specialty_id' => $specialty->id,
                    'to_specialty_id'   => $toSpecialtyId,
                ]
            );

            app(PromotionRunLogStore::class)->putStudent(
                $studentPromotionRecord->id,
                $student->id,
                $schoolBranchId,
                $rules->map(fn($rule) => $rule->toArray())->values()->all(),
            );
        }

        if ($this->wasCancelled($promotionRun)) {
            $this->stopCancelled($promotionRun, $log, $students->count(), $processed, $passedCount, $failedCount, $needsReviewCount);
            return;
        }

        $promotionRun->update([
            'total_students' => $students->count(),
            'processed_students' => $processed,
            'promoted_count' => $passedCount,
            'repeated_count' => $failedCount,
            'review_count' => $needsReviewCount,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $log->save($promotionRun, 'completed', 'Promotion run completed', [
            'total' => $students->count(),
            'processed' => $processed,
            'promoted' => $passedCount,
            'retained' => $failedCount,
            'needs_review' => $needsReviewCount,
        ]);

        if ($specialty->graduation_level) {
            StudentGraduationJob::dispatch($runId, $schoolBranchId);
        }
    }

    /**
     * Checks everything the run needs before evaluating anyone, and records
     * every problem found rather than stopping at the first.
     *
     * @return array{0: ?Specialty, 1: Collection, 2: ?Specialty}
     */
    private function checkCanRun(PromotionRun $promotionRun, PromotionRunLogBuilder $log): array
    {
        $schoolYear = $promotionRun->schoolYear;
        $specialty = $schoolYear?->specialty;
        $students = $specialty?->student ?? collect();

        if (!$schoolYear || !$specialty) {
            $log->error(
                'Academic year not found',
                'The academic year or specialty this run promotes from no longer exists.',
                ['school_year_id' => $promotionRun->school_year_id]
            );
            return [$specialty, $students, null];
        }

        $from = $this->describeSpecialty($specialty);

        if ($students->isEmpty()) {
            $log->error(
                'No students to promote',
                "{$from} has no enrolled students, so there is no one to promote.",
                ['specialty' => $this->specialtyDetails($specialty)]
            );
        }

        $levelSettings = $this->levelSetting($promotionRun->school_branch_id);
        $levels = $levelSettings?->value
            ? Educationlevels::whereHas("levelType", fn($query) => $query->where("program_name", $levelSettings->value))->get()
            : collect();

        if (!$levelSettings?->value) {
            $log->error(
                'Level setup missing',
                'The school has not chosen its level system (Level settings), so the next level cannot be worked out.',
                ['setting' => 'level.start']
            );
            return [$specialty, $students, null];
        }

        if ($levels->isEmpty()) {
            $log->error(
                'Level setup has no levels',
                "No levels exist for the chosen level system '{$levelSettings->value}', so the next level cannot be worked out.",
                ['level_system' => $levelSettings->value]
            );
            return [$specialty, $students, null];
        }

        $currentLevel = (int) $specialty->level?->level;
        $nextLevel = $levels->filter(fn($level) => (int) $level->level > $currentLevel)->sortBy(fn($level) => (int) $level->level)->first();

        if (!$nextLevel) {
            $log->error(
                'No next level',
                "{$from} is at the highest level of the school's level system, so there is no level to promote to.",
                ['specialty' => $this->specialtyDetails($specialty)]
            );
            return [$specialty, $students, null];
        }

        $nextSpecialty = $this->getNextSpecialty($specialty, $levels);

        if (!$nextSpecialty) {
            $log->error(
                'Next level specialty missing',
                "There is no {$specialty->specialty_name} specialty at {$nextLevel->name} to promote students into. Create it, then retry the run.",
                ['specialty' => $this->specialtyDetails($specialty), 'next_level' => ['name' => $nextLevel->name, 'level' => $nextLevel->level]]
            );
            return [$specialty, $students, null];
        }

        $activeYear = SchoolAcademicYear::where("school_branch_id", $promotionRun->school_branch_id)
            ->with(['systemAcademicYear'])
            ->where("specialty_id", $nextSpecialty->id)
            ->where("end_date", ">=", now())
            ->first();

        if ($activeYear) {
            $yearName = $activeYear->systemAcademicYear?->name ?? 'an academic year';
            $log->error(
                'Next level is already running',
                "{$this->describeSpecialty($nextSpecialty)} already has {$yearName} in progress or planned (until "
                    . Carbon::parse($activeYear->end_date)->format('j M Y')
                    . '). Students can only be promoted into it once it ends.',
                [
                    'next_specialty' => $this->specialtyDetails($nextSpecialty),
                    'academic_year' => [
                        'name' => $activeYear->systemAcademicYear?->name,
                        'start_date' => substr((string) $activeYear->start_date, 0, 10),
                        'end_date' => substr((string) $activeYear->end_date, 0, 10),
                    ],
                ]
            );
        }

        return [$specialty, $students, $nextSpecialty];
    }

    private function describeSpecialty(Specialty $specialty): string
    {
        return trim("{$specialty->specialty_name} " . ($specialty->level?->name ?? ''));
    }

    private function specialtyDetails(Specialty $specialty): array
    {
        return [
            'name' => $specialty->specialty_name,
            'level_name' => $specialty->level?->name,
            'level' => $specialty->level?->level,
        ];
    }

    private function wasCancelled(PromotionRun $promotionRun): bool
    {
        return PromotionRun::whereKey($promotionRun->id)->value('status') === 'cancelled';
    }

    /** Saves how far a cancelled run got and logs it; its records so far are kept. */
    private function stopCancelled(PromotionRun $promotionRun, PromotionRunLogBuilder $log, int $total, int $processed, int $passed, int $failed, int $review): void
    {
        $promotionRun->forceFill([
            'total_students' => $total,
            'processed_students' => $processed,
            'promoted_count' => $passed,
            'repeated_count' => $failed,
            'review_count' => $review,
        ])->save();

        $log->save($promotionRun, 'cancelled', 'Promotion run cancelled', [
            'total' => $total, 'processed' => $processed, 'promoted' => $passed, 'retained' => $failed, 'needs_review' => $review,
        ]);
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
    private function getPromotionSetting(string $schoolBranchId)
    {

        return SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination.settingCategory'])
            ->whereHas('settingDefination.settingCategory', function ($query) {
                $query->where("key", "setting.category.promotion");
            })
            ->get();
    }
    private function levelSetting(string $schoolBranchId)
    {
        return  SchoolBranchSetting::where("school_branch_id", $schoolBranchId)
            ->with(['settingDefination'])
            ->whereHas('settingDefination', function ($query) {
                $query->where("key", "level.start");
            })->first();
    }
}
