<?php

namespace App\Services\Analytics\Academic\Widget\Resit;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;
use App\Services\Analytics\Academic\Aggregates\Resit\LevelResitAggregate;

class LevelResit
{
    protected LevelResitAggregate $levelResitAggregate;
    public function __construct(LevelResitAggregate $levelResitAggregate)
    {
        $this->levelResitAggregate = $levelResitAggregate;
    }

    public function getResitLevel($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::SCHOOL_RESIT_TOTAL
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->levelResitAggregate->calculate($query);
    }
}
