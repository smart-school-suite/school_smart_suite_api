<?php

namespace App\Services\Analytics\Academic\Widget\Resit;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Resit\ExamTypeResitAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class ExamTypeResit
{
    protected ExamTypeResitAggregate $examTypeResitAggregate;
    public function __construct(ExamTypeResitAggregate $examTypeResitAggregate)
    {
        $this->examTypeResitAggregate = $examTypeResitAggregate;
    }

    public function getExamTypeResit($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_RESIT_TOTAL
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->examTypeResitAggregate->calculate($query);
    }
}
