<?php

namespace App\Services\Analytics\Academic\Widget\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Gpa\LevelAverageGpaAggregator;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class LevelAverageGpa
{
    protected LevelAverageGpaAggregator $levelAverageGpaAggregator;
    public function __construct(LevelAverageGpaAggregator $levelAverageGpaAggregator)
    {
        $this->levelAverageGpaAggregator = $levelAverageGpaAggregator;
    }

    public function getLevelAverageGpa($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_GPA
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->levelAverageGpaAggregator->calculate($query);
    }
}
