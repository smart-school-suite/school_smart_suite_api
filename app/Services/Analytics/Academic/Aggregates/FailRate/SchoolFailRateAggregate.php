<?php

namespace App\Services\Analytics\Academic\Aggregates\FailRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolFailRateAggregate
{
    public function calculate(Collection $query)
    {
        $totalSat = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)->sum("value");
        $totalFailed = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED)->sum("value");

        $failRate = ($totalSat > 0)
            ? ($totalFailed / $totalSat) * 100
            : 0;

        return [
            "total_sat" => $totalSat,
            "total_failed" => $totalFailed,
            "fail_rate" => round($failRate, 2)
        ];
    }
}
