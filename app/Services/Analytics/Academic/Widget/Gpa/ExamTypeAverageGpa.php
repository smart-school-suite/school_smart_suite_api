<?php

namespace App\Services\Analytics\Academic\Widget\Gpa;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Gpa\ExamTypeAverageGpaAggregator;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class ExamTypeAverageGpa
{
    protected ExamTypeAverageGpaAggregator $examTypeAverageGpaAggregator;
    public function __construct(ExamTypeAverageGpaAggregator $examTypeAverageGpaAggregator)
    {
        $this->examTypeAverageGpaAggregator = $examTypeAverageGpaAggregator;
    }
    public function getExamTypeAverageGpa($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_GPA
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->examTypeAverageGpaAggregator->calculate($query);
    }
}
