<?php

namespace App\Services\Analytics\Academic\Widget\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\PassRate\SchoolPassRateAggregate;
use App\Services\Analytics\Academic\Filters\SchoolPassRateFilter;
class SchoolPassRate
{
    protected SchoolPassRateAggregate $schoolPassRateAggregate;
    protected SchoolPassRateFilter $schoolPassRateFilter;
    public function __construct(SchoolPassRateAggregate $schoolPassRateAggregate, SchoolPassRateFilter $schoolPassRateFilter)
    {
        $this->schoolPassRateAggregate = $schoolPassRateAggregate;
        $this->schoolPassRateFilter = $schoolPassRateFilter;
    }
    public function getSchoolPassRate($currentSchool, $year, $filters)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_PASSED
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);

        $filteredQuery = $this->schoolPassRateFilter->apply($query, $filters);
        return $this->schoolPassRateAggregate->calculate($filteredQuery, $filters);
    }
}
