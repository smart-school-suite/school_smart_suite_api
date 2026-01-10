<?php

namespace App\Services\Analytics\Academic\Widget\Resit;

use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\Resit\ResitTotalAggregate;
use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;

class SchoolResitTotal
{
    protected ResitTotalAggregate $resitTotalAggregate;
    public function __construct(ResitTotalAggregate $resitTotalAggregate)
    {
        $this->resitTotalAggregate = $resitTotalAggregate;
    }
    public function getTotalResit($currentSchool, $year)
    {
        $targetKpis = [
            AcademicAnalyticsKpi::RESIT
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->resitTotalAggregate->calculate($query);
    }
}
