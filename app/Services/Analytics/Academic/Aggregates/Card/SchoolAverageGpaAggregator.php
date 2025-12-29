<?php

namespace App\Services\Analytics\Academic\Aggregates\Card;

use Illuminate\Database\Query\Builder;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class SchoolAverageGpaAggregator
{
    public function calculate(Builder $query)
    {
        $totalCandidate = $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE)
            ->sum('value');
        $totalGpa =  $query->where("kpi", AcademicAnalyticsKpi::SCHOOL_GPA)
            ->sum('value');
        return   round(($totalGpa / $totalCandidate), 2);
    }
}
