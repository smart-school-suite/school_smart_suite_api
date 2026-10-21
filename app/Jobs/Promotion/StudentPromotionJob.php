<?php

namespace App\Jobs\Promotion;

use App\DTOs\Promotion\PromotionRunLogReasonDTO;
use App\DTOs\Promotion\PromotionRunLogDTO;
use App\Models\AdditionalFees;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\Promotion\PromotionRun;
use App\Models\Promotion\StudentPromotionLog;
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
        $reasons = [];

        $promotionRun = PromotionRun::where("school_branch_id", $schoolBranchId)
            ->where("id", $runId)
            ->with(['schoolYear.specialty.student', 'schoolYear.specialty.level'])
            ->first();

        if (!$promotionRun) {
            $reasons[] = new PromotionRunLogReasonDTO(
                reason: "Promotion run record not found.",
                details: ['promotion_run_id' => $runId, 'school_branch_id' => $schoolBranchId]
            );
            $this->failAndLog($runId, $schoolBranchId, "Promotion Run Initialization Failed", $reasons);
            return;
        }

        $specialty = $promotionRun->schoolYear?->specialty;
        $students = $specialty?->student ?? collect();

        if ($students->isEmpty()) {
            $reasons[] = new PromotionRunLogReasonDTO(
                reason: "No students found attached to the current specialty/school year.",
                details: ['specialty' => $specialty ?? null]
            );
            $this->failAndLog($runId, $schoolBranchId, "No Students Found", $reasons, $promotionRun);
            return;
        }

        $levelSettings = $this->levelSetting($schoolBranchId);

        $levels = Educationlevels::whereHas(
            "levelType",
            fn($query) => $query->where("program_name", $levelSettings?->value)
        )->get();

        if ($levels->isEmpty()) {
            $reasons[] = new PromotionRunLogReasonDTO(
                reason: "No education levels found matching program name.",
                details: ['program_name' => $levelSettings?->value]
            );
            $this->failAndLog($runId, $schoolBranchId, "Level Settings Misconfiguration", $reasons, $promotionRun);
            return;
        }

        $nextSpecialty = $this->getNextSpecialty($specialty, $levels);

        if (!$nextSpecialty) {
            $reasons[] = new PromotionRunLogReasonDTO(
                reason: "Target next specialty could not be determined.",
                details: [
                    'current_specialty' =>  [
                        'id' => $specialty->id ?? null,
                        "name" => $specialty->specialty_name ?? null,
                        "level_name" => $specialty->level->name ?? null,
                        "level_number" => $specialty->level->level ?? null
                    ] ?? null,
                ]
            );
            $this->failAndLog($runId, $schoolBranchId, "Next Specialty Missing", $reasons, $promotionRun);
            return;
        }

        $nextSpecialtySchoolYear = SchoolAcademicYear::where("school_branch_id", $schoolBranchId)
            ->with(["specialty", "level", 'systemAcademicYear'])
            ->where("specialty_id", $nextSpecialty->id)
            ->where("end_date", ">=", now())
            ->first();

        $nextSpecialtyName = $nextSpecialty->specialty_name ?? 'Unknown Specialty';
        $currentSpecialtyName = $specialty->specialty_name ?? 'Unknown Specialty';

        if ($nextSpecialtySchoolYear) {
            $reasons[] = new PromotionRunLogReasonDTO(
                reason: "Active academic year already exists for target specialty '{$nextSpecialtyName}'. Unable to promote from '{$currentSpecialtyName}'.",
                details: [
                    'next_specialty' => [
                        'id' => $nextSpecialty->id ?? null,
                        "name" => $nextSpecialty->specialty_name ?? null,
                        "level_name" => $nextSpecialty->level->name ?? null,
                        "level_number" => $nextSpecialty->level->level ?? null
                    ] ?? null,
                    'current_specialty' =>  [
                        'id' => $specialty->id ?? null,
                        "name" => $specialty->specialty_name ?? null,
                        "level_name" => $specialty->level->name ?? null,
                        "level_number" => $specialty->level->level ?? null
                    ] ?? null,
                    'academic_year' => [
                        'id' => $nextSpecialtySchoolYear->id ?? null,
                        'start_date' => $nextSpecialtySchoolYear->start_date ?? null,
                        'end_date' => $nextSpecialtySchoolYear->end_date ?? null,
                        'academic_year' => $nextSpecialtySchoolYear->systemAcademicYear->name ?? null

                    ] ?? null,
                ]
            );
            $this->failAndLog($runId, $schoolBranchId, "Target Specialty Currently Active", $reasons, $promotionRun);
            return;
        }

        $reasons[] = new PromotionRunLogReasonDTO(
            reason: "Target specialty '{$nextSpecialtyName}' is free (no active academic year found). Proceeding with promotion.",
            details: [
                'next_specialty' => [
                    'id' => $nextSpecialty->id ?? null,
                    "name" => $nextSpecialty->specialty_name ?? null,
                    "level_name" => $nextSpecialty->level->name ?? null,
                    "level_number" => $nextSpecialty->level->level ?? null
                ] ?? null,
                'current_specialty' =>  [
                    'id' => $specialty->id ?? null,
                    "name" => $specialty->specialty_name ?? null,
                    "level_name" => $specialty->level->name ?? null,
                    "level_number" => $specialty->level->level ?? null
                ] ?? null,
                'academic_year' => $nextSpecialtySchoolYear ?? null,
            ]
        );

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

        foreach ($students as $student) {
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

            StudentPromotionLog::updateOrCreate(
                [
                    'record_id'        => $studentPromotionRecord->id,
                    'student_id'       => $student->id,
                    'school_branch_id' => $schoolBranchId,
                ],
                [
                    'rule_logs' => $rules
                        ->map(fn($rule) => $rule->toArray())
                        ->values()
                        ->all(),
                ]
            );
        }

        $promotionRun->update([
            'passed_count' => $passedCount,
            'failed_count' => $failedCount,
            'needs_review_count' => $needsReviewCount,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $reasons[] = new PromotionRunLogReasonDTO(
            reason: "Promotion run completed successfully.",
            details: [
                'total_students' => $students->count(),
                'passed' => $passedCount,
                'failed' => $failedCount,
                'needs_review' => $needsReviewCount,
            ]
        );

        $logDto = new PromotionRunLogDTO(
            status: 'success',
            title: 'Promotion Run Execution Completed',
            reasons: $reasons
        );

        PromotionRunLog::updateOrCreate(
            [
                'promotion_run_id' => $runId,
                'school_branch_id' => $schoolBranchId,
            ],
            [
                'rule_logs' => $logDto->toArray(),
            ]
        );

        if ($specialty->graduation_level) {
            StudentGraduationJob::dispatch($runId, $schoolBranchId);
        }
    }

    private function failAndLog(
        int|string $runId,
        int|string $schoolBranchId,
        string $title,
        array $reasons,
        ?PromotionRun $promotionRun = null
    ): void {
        if ($promotionRun) {
            $promotionRun->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
        }

        $logDto = new PromotionRunLogDTO(
            status: 'failed',
            title: $title,
            reasons: $reasons
        );

        PromotionRunLog::updateOrCreate(
            [
                'promotion_run_id' => $runId,
                'school_branch_id' => $schoolBranchId,
            ],
            [
                'rule_logs' => $logDto,
            ]
        );
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
