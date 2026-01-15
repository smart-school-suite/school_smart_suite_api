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
            $examData = $query->where("exam_type_id", $examType->id);

            $totalCandidate = $examData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
                ->sum("value");

            $totalPassed = $examData->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED)
                ->sum("value");

            $passRate = ($totalCandidate > 0)
                ? ($totalPassed / $totalCandidate) * 100
                : 0;

            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "semester" => $examType->semester ?? "unknown",
                "total_candidate" => $totalCandidate,
                "total_passed" => $totalPassed,
                "pass_rate" => round($passRate, 2)
            ];
        });
    }
}
