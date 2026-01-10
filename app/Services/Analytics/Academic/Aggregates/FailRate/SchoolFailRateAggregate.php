<?php

namespace App\Services\Analytics\Academic\Aggregates\FailRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolFailRateAggregate
{
    public function calculate(Collection $query)
    {
        $totalSat = $query->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)->sum("value");
        $totalFailed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED)->sum("value");
        return [
             "total_sat" => $totalSat,
             "total_failed" => $totalFailed,
             "fail_rate" => round($totalSat / $totalFailed * 100, 2)
        ];
    }

}
