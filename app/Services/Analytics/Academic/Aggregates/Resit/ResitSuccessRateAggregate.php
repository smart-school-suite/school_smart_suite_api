<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class ResitSuccessRateAggregate
{
    public static function calculate(Collection $query)
    {
        $totalSat = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_RESIT_CANDIDATE)->sum("value");
        $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_RESIT_CANDIDATE_PASSED)->sum("value");

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
