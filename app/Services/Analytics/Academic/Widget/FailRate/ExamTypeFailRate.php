<?php

namespace App\Services\Analytics\Academic\Widget\FailRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\FailRate\ExamTypeFailRateAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class ExamTypeFailRate
{
    protected ExamTypeFailRateAggregate $examTypeFailRateAggregate;
    public function __construct(ExamTypeFailRateAggregate $examTypeFailRateAggregate)
    {
        $this->examTypeFailRateAggregate = $examTypeFailRateAggregate;
    }

    public function getExamTypeFailRate($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_EXAM_CANDIDATE_FAILED
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->examTypeFailRateAggregate->calculate($query);
    }
}
