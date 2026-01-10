<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Models\Examtype;
use Illuminate\Support\Collection;

class ExamTypePassRateAggregate
{
    public static function calculate(Collection $query)
    {
        $examTypes = Examtype::where("type", "!=", "resit")->get();
        return $examTypes->map(function ($examType) use ($query) {
            $totalCandidate = $query->where("exam_type_id", $examType->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum("value");
            $totalPassed = $query->where("exam_type_id", $examType->id)
                ->where("kpi", AcademicAnalyticskpi::EXAM_COURSE_CANDIDATE_PASSED)
                ->sum("value");
            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "semester" => $examType->semester ?? "unknown",
                "total_candidate" => $totalCandidate,
                "total_passed" => $totalPassed,
                "pass_rate" => round($totalPassed / $totalCandidate * 100, 2)
            ];
        });
    }
}
