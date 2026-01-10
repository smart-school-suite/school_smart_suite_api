<?php

namespace App\Services\Analytics\Academic\Widget\FailRate;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\FailRate\LevelFailRateAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class LevelFailRate
{
    protected LevelFailRateAggregate $levelFailRateAggregate;
    public function __construct(LevelFailRateAggregate $levelFailRateAggregate)
    {
       $this->levelFailRateAggregate = $levelFailRateAggregate;
    }
    public function getLevelFailRate($currentSchool, $year){
         $kpis = [
            AcademicAnalyticsKpi::EXAM_CANDIDATE,
            AcademicAnalyticsKpi::EXAM_COURSE_CANDIDATE_FAILED
         ];

         $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
         return $this->levelFailRateAggregate->calculate($query);
    }
}
