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
    public function getResitSuccessRate($currentSchool, $year, $filters)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::RESIT_EXAM_CANDIDATE,
            AcademicAnalyticsKpi::RESIT_EXAM_PASSED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        if(!empty($filters)){
            $query = $this->resitSuccessRateFilter->apply($query, $filters);
        }

        return $this->resitSuccessRateAggregate->calculate($query, $filters);
    }
}
