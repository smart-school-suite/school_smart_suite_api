<?php

namespace App\Services\Analytics\Academic\Widget\Grade;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Grade\SchoolGradeDistributionAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class SchoolGradeDistribution
{
    protected SchoolGradeDistributionAggregate $schoolGradeDistributionAggregate;
    public function __construct(SchoolGradeDistributionAggregate $schoolGradeDistributionAggregate)
    {
        $this->schoolGradeDistributionAggregate = $schoolGradeDistributionAggregate;
    }
    public function getSchoolGradeDistribution($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_GRADE_DISTRIBUTION
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->schoolGradeDistributionAggregate->calculate($query);
    }
}
