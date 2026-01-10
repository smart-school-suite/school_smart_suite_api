<?php

namespace App\Services\Analytics\Academic\Widget\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\PassRate\ExamTypePassRateAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class ExamTypePassRate
{
    protected ExamTypePassRateAggregate $examTypePassRateAggregate;
    public function __construct(ExamTypePassRateAggregate $examTypePassRateAggregate)
    {
        $this->examTypePassRateAggregate = $examTypePassRateAggregate;
    }

    public function getExamTypePassRate($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::EXAM_PASSED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->examTypePassRateAggregate->calculate($query);
    }
}
