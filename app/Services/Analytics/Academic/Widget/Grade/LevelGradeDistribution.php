<?php

namespace App\Services\Analytics\Academic\Widget\Grade;

use App\Constant\Analytics\Academic\AcademicAnalyticsKpi;
use App\Services\Analytics\Academic\Aggregates\Grade\LevelGradeDistributionAggregate;
use App\Services\Analytics\Academic\Query\AcademicAnalyticQuery;

class LevelGradeDistribution
{
    protected LevelGradeDistributionAggregate $levelGradeDistributionAggregate;
    public function __construct(LevelGradeDistributionAggregate $levelGradeDistributionAggregate)
    {
        $this->levelGradeDistributionAggregate  = $levelGradeDistributionAggregate;
    }

    public function getLevelGradeDistribution($currentSchool, $year)
    {
        $kpis = [
            AcademicAnalyticsKpi::EXAM_GRADE_DISTRIBUTION
        ];

        $query = AcademicAnalyticQuery::base($currentSchool->id, $year, $kpis);
        return $this->levelGradeDistributionAggregate->calculate($query);
    }
}
