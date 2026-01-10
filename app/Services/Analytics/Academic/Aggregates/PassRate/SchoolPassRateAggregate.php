<?php

namespace App\Services\Analytics\Academic\Aggregates\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolPassRateAggregate
{
    public static function calculate(Collection $query){
          $totalSat = $query->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)->sum("value");
          $totalPassed = $query->where("kpi", AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_PASSED)->sum("value");
          return [
             "total_sat" => $totalSat,
             "total_passed" => $totalPassed,
             "pass_rate" => round($totalPassed / $totalSat)
          ];
    }
}
