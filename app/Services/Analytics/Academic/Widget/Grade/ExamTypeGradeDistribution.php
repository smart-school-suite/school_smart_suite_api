<?php

namespace App\Services\Analytics\Academic\Widget\Grade;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Grade\ExamTypeGradeDistributionAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class ExamTypeGradeDistribution
{
    protected ExamTypeGradeDistributionAggregate $examTypeGradeDistributionAggregate;
    public function __construct(ExamTypeGradeDistributionAggregate $examTypeGradeDistributionAggregate)
    {
        $this->examTypeGradeDistributionAggregate = $examTypeGradeDistributionAggregate;
    }

    public function getExamTypeGradeDistribution($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::EXAM_GRADE_DISTRIBUTION,
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->examTypeGradeDistributionAggregate->calculate($query);
    }
}
