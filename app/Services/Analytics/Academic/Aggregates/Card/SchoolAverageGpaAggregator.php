<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;


use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolAverageGpaAggregator
{
    public function calculate(Collection $query)
    {
        $totalCandidate = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
            ->sum('value');
        $totalGpa =  $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)
            ->sum('value');
        return   round(($totalGpa / $totalCandidate), 2);
    }
}
