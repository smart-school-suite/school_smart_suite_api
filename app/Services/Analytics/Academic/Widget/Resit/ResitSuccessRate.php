<?php

namespace App\Services\Analytics\Academic\Widget\Resit;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Filters\ResitSuccessRateFilter;
use App\Services\Analytics\Academic\Aggregates\Resit\ResitSuccessRateAggregate;

class ResitSuccessRate
{
    protected ResitSuccessRateFilter $resitSuccessRateFilter;
    protected ResitSuccessRateAggregate $resitSuccessRateAggregate;
    public function __construct(ResitSuccessRateFilter $resitSuccessRateFilter, ResitSuccessRateAggregate $resitSuccessRateAggregate)
    {
        $this->resitSuccessRateFilter = $resitSuccessRateFilter;
        $this->resitSuccessRateAggregate = $resitSuccessRateAggregate;
    }
    public function getResitSuccessRate($currentSchool, $year)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::SCHOOL_RESIT_CANDIDATE,
            AcademicAnalyticsKpi::SCHOOL_RESIT_CANDIDATE_PASSED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->resitSuccessRateAggregate->calculate($query);
    }
}
