<?php

namespace App\Services\Analytics\Academic\Aggregates\FailRate;

use App\Models\Examtype;
use Illuminate\Support\Collection;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class ExamTypeFailRateAggregate
{
    public static function calculate(Collection $query)
    {
        $examTypes = Examtype::where("type", "!=", "resit")->get();
        return $examTypes->map(function ($examType) use ($query) {
            $totalSat = $query->where("exam_type_id", $examType->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)
                ->sum("value");
            $totalPassed = $query->where("level_id", $examType->id)
                ->where("kpi", AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED)
                ->sum("value");
            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "semester" => $examType->semester ?? "unknown",
                "total_sat" => $totalSat,
                "total_passed" => $totalPassed,
                "pass_rate" => self::passRate($totalSat, $totalPassed)
            ];
        });
    }
    protected static function passRate($totalSat, $totalPassed)
    {
        return round($totalPassed / $totalSat * 100);
    }
}
