<?php

namespace App\Jobs\Promotion;

use App\Models\AdditionalFees;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolBranchSetting;
use App\Models\Studentresit;
use App\Models\TuitionFees;
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

        $examTypes = Examtype::with(['semesters' => function ($query) {
            $query->where("count", "<", 3)
                ->where("type", "!=", "resit");
        }])->get();

        $promotionRun = PromotionRun::where("school_branch_id", $schoolBranchId)
            ->where("id", $runId)
            ->with(['schoolYear.specialty.student', 'schoolYear.specialty.level'])
            ->firstOrFail();

        $studentIds = $promotionRun->schoolYear->specialty->student->pluck('id');

        $studentMarks = ExamScore::where("school_branch_id", $schoolBranchId)
            ->whereHas('candidate', function ($q) use ($studentIds) {
                $q->whereIn("student_id", $studentIds);
            })
            ->with(['exam.semester', 'candidate.student', 'course', 'exam.examType'])
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
            ->with(['student', 'course'])
         ->get();

        $schoolYear = $promotionRun->schoolYear;
        $specialty = $promotionRun->schoolYear->specialty;
        $promotionSetting = $this->getPromotionSetting($schoolBranchId);
        $students = $promotionRun->schoolYear->specialty->student;


        //
        foreach ($students as $student) {
            //studentscores
            $scores = $studentMarks->filter(fn($marks) => $marks->candidate?->student_id === $student->id);
            $resits = $studentResits->where("student_id",  $student->id);
            $tuitionFees = $studentTuitionFees->where("student_id", $student->id);
            $additionalFees = $studentAdditionalFees->where("student_id", $student->id);
        }
    }
    private function evaluateAdditionalFeePromCondition(Collection $additionalFees, Collection $promotionSettings)
    {
        $maxDebtSetting = $promotionSettings->first(fn($s) => $s->settingDefination?->key === "promotion.max_additional_fee_debt");
        $maxAllowedDebt = $maxDebtSetting ? (float) $maxDebtSetting->value : 0.0;

        $totalDebt = 0.0;
        $unpaidFeeDetails = [];

        foreach ($additionalFees as $fee) {
            $requiredAmount = (float) ($fee->amount ?? 0);
            $totalPaid = (float) $fee->additionalFeeTranctions->sum('amount');
            $balance = max(0, $requiredAmount - $totalPaid);

            if ($balance > 0) {
                $totalDebt += $balance;
                $unpaidFeeDetails[] = [
                    'fee_id'    => $fee->id,
                    'reason'    => $fee->reason ?? 'Unspecified Fee',
                    'required'  => $requiredAmount,
                    'paid'      => $totalPaid,
                    'balance'   => $balance,
                ];
            }
        }

        $hasPassed = $totalDebt <= $maxAllowedDebt;

        $messages = [];
        if ($hasPassed) {
            if ($totalDebt === 0.0) {
                $messages[] = "Student passed additional fee promotion criteria with zero outstanding balance.";
            } else {
                $messages[] = "Student passed additional fee promotion criteria with an outstanding debt of {$totalDebt} (Maximum allowed: {$maxAllowedDebt}).";
            }
        } else {
            $messages[] = "Student failed additional fee promotion criteria with an outstanding debt of {$totalDebt}, which exceeds the maximum allowed debt limit of {$maxAllowedDebt}.";
        }

        return [
            'status' => $hasPassed,
            'messages' => $messages,
            'total_debt' => $totalDebt,
            'max_allowed_debt' => $maxAllowedDebt,
            'unpaid_fees' => $unpaidFeeDetails,
        ];
    }
    private function evaluateTuitionFeePromCondition(Collection $tuitionFee, Collection $promotionSettings)
    {
        $promMaxTuitionFeeDebt = $promotionSettings->first(fn($s) => $s->settingDefination->key === "promotion.max_tuition_fee_debt");
    }
    private function evaluateCarryOverPromCondition(Collection $resits, Collection $promotionSettings)
    {
        $maxCarryOversSetting = $promotionSettings->first(fn($s) => $s->settingDefination?->key === "promotion.max_carry_overs");
        $maxAllowedCarryOvers = $maxCarryOversSetting ? (int) $maxCarryOversSetting->value : 0;

        $carryOverCount = $resits->filter(fn($resit) => (bool) $resit->iscarry_over)->count();

        $hasPassed = $carryOverCount <= $maxAllowedCarryOvers;

        $messages = [];
        if ($hasPassed) {
            if ($carryOverCount === 0) {
                $messages[] = "Student passed resit promotion criteria with zero carry-overs.";
            } else {
                $messages[] = "Student passed resit promotion criteria with {$carryOverCount} carry-over(s) (Maximum allowed: {$maxAllowedCarryOvers}).";
            }
        } else {
            $messages[] = "Student failed resit promotion criteria with {$carryOverCount} carry-over(s), which exceeds the maximum limit of {$maxAllowedCarryOvers}.";
        }

        return [
            'status' => $hasPassed,
            'messages' => $messages,
            'carry_over_count' => $carryOverCount,
            'max_allowed_carry_overs' => $maxAllowedCarryOvers,
        ];
    }
    private function evaluateScorePromCondition(Collection $scores, Collection $promotionSettings, Collection $examTypes)
    {
        $promMinGpaSetting = $promotionSettings->first(fn($s) => $s->settingDefination?->key === "promotion.min_gpa");
        $promMinGpa = $promMinGpaSetting ? (float) $promMinGpaSetting->value : null;

        if ($promMinGpa === null) {
            return [
                'status' => false,
                'messages' => ['Promotion minimum GPA setting is not defined.'],
                'average_gpa' => 0,
                'exam_type_gpas' => [],
            ];
        }

        $scoresByExamType = $scores->groupBy(fn($score) => $score->exam?->exam_type_id);

        $gpaByExamType = [];
        $totalGpaSum = 0;
        $satExamTypesCount = 0;

        foreach ($examTypes as $examType) {
            $typeScores = $scoresByExamType->get($examType->id, collect());

            if ($typeScores->isEmpty()) {
                continue;
            }

            $totalWeightedPoints = 0;
            $totalCredits = 0;

            foreach ($typeScores as $scoreItem) {
                $obtainedScore = $scoreItem->score ?? 0;
                $maxScore = $scoreItem->exam?->max_score ?? 0;
                $credits = $scoreItem->course?->credit ?? 0;

                if ($maxScore > 0 && $credits > 0) {
                    $percentage = ($obtainedScore / $maxScore) * 100;
                    $gradePoint = ($percentage / 100) * 4.0;

                    $totalWeightedPoints += ($gradePoint * $credits);
                    $totalCredits += $credits;
                }
            }

            if ($totalCredits > 0) {
                $gpa = round($totalWeightedPoints / $totalCredits, 2);
                $gpaByExamType[$examType->id] = [
                    'name' => $examType->name ?? "Exam Type #{$examType->id}",
                    'gpa'  => $gpa,
                ];
                $totalGpaSum += $gpa;
                $satExamTypesCount++;
            }
        }

        if ($satExamTypesCount === 0) {
            return [
                'status' => false,
                'messages' => ['Student has no valid exam scores or credits to calculate GPA.'],
                'average_gpa' => 0,
                'exam_type_gpas' => [],
            ];
        }

        $overallAverageGpa = round($totalGpaSum / $satExamTypesCount, 2);
        $hasPassed = $overallAverageGpa >= $promMinGpa;

        $messages = [];
        if ($hasPassed) {
            $messages[] = "Student passed promotion criteria with an average GPA of {$overallAverageGpa} across {$satExamTypesCount} exam types (Required: {$promMinGpa}).";
        } else {
            $messages[] = "Student failed promotion criteria. Achieved average GPA of {$overallAverageGpa} across {$satExamTypesCount} exam types, which is below the required minimum of {$promMinGpa}.";
        }

        return [
            'status' => $hasPassed,
            'messages' => $messages,
            'average_gpa' => $overallAverageGpa,
            'required_min_gpa' => $promMinGpa,
            'exam_type_gpas' => $gpaByExamType,
        ];
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
}
