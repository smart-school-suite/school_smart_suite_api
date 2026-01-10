<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class ResitSuccessRateAggregate
{
    public static function calculate(Collection $query)
    {
        $totalSat = $query->where("kpi", AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE)->sum("value");
        $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::RESIT_EXAM_PASSED)->sum("value");
        return  [
            "total_sat" => $totalSat,
            "total_passed" => $totalPassed,
            "pass_rate" => round($totalPassed / $totalSat * 100, 2)
        ];
    }
}
