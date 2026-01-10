<?php

namespace App\Services\Analytics\Academic\Aggregates\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolAverageGpaAggregator
{
    public static function calculate(Collection $query)
    {
        $totalCandidate = $query->where("kpi", AcademicAnalyticsKpi::EXAM_CANDIDATE)->sum("value");
        $totalGpa = $query->where("kpi", AcademicAnalyticsKpi::EXAM_GPA)->sum("value");
        return [
            "total_candidate" => $totalCandidate,
            "total_gpa" => $totalGpa,
            "average_gpa" => round($totalGpa / $totalCandidate * 100, 2)
        ];
    }
}
