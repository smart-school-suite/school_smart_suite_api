<?php

namespace App\Services\Analytics\Academic\Widget\PassRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\PassRate\LevelPassRateAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class LevelPassRate
{
    protected LevelPassRateAggregate $levelPassRateAggregate;
    public function __construct(LevelPassRateAggregate $levelPassRateAggregate)
    {
        $this->levelPassRateAggregate = $levelPassRateAggregate;
    }

    public function getLevelPassRate($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::EXAM_PASSED
        ];
        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->levelPassRateAggregate->calculate($query);
    }
}
