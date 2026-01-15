<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolPassRateAggregate
{
    public static function calculate(Collection $query)
    {
        $totalSat = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)->sum("value");
        $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED)->sum("value");

        $passRate = ($totalSat > 0)
            ? ($totalPassed / $totalSat) * 100
            : 0;

        return [
            "total_sat" => $totalSat,
            "total_passed" => $totalPassed,
            "pass_rate" => round($passRate, 2)
        ];
    }
}
