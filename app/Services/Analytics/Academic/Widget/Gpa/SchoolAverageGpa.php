<?php

namespace App\Services\Analytics\Academic\Widget\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Gpa\SchoolAverageGpaAggregator;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class SchoolAverageGpa
{
    protected SchoolAverageGpaAggregator $schoolAverageGpaAggregator;
    public function __construct(SchoolAverageGpaAggregator $schoolAverageGpaAggregator)
    {
        $this->schoolAverageGpaAggregator = $schoolAverageGpaAggregator;
    }

    public function getSchoolAverageGpa($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::EXAM_GPA,
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->schoolAverageGpaAggregator->calculate($query);
    }
}
