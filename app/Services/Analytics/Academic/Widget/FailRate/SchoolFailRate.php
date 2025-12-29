<?php

namespace App\Services\Analytics\Academic\Widget\FailRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\FailRate\SchoolFailRateAggregate;
use App\Services\Analytics\Academic\Filters\SchoolFailRateFilter;

class SchoolFailRate
{
    protected SchoolFailRateAggregate $schoolFailRateAggregate;
    protected SchoolFailRateFilter $schoolFailRateFilter;
    public function __construct(SchoolFailRateAggregate $schoolFailRateAggregate, SchoolFailRateFilter $schoolFailRateFilter)
    {
        $this->schoolFailRateAggregate = $schoolFailRateAggregate;
        $this->schoolFailRateFilter = $schoolFailRateFilter;
    }
    public function getSchoolFailRate($currentSchool, $year, $filters)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        $filteredQuery = $this->schoolFailRateFilter->apply($query, $filters);
        return $this->schoolFailRateAggregate->calculate($filteredQuery, $filters);
    }
}
