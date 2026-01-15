<?php

namespace App\Services\Analytics\Academic\Aggregates\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolAverageGpaAggregator
{
    public static function calculate(Collection $query)
    {
        $totalCandidate = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)->sum("value");
        $totalGpa = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)->sum("value");

        $averageGpa = ($totalCandidate > 0)
            ? ($totalGpa / $totalCandidate)
            : 0;

        return [
            "total_candidate" => $totalCandidate,
            "total_gpa" => $totalGpa,
            "average_gpa" => round($averageGpa, 2)
        ];
    }
}
