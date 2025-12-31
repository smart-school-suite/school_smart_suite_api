<?php

namespace App\Services\Analytics\Operational\Widget\Hall;

use App\Services\Analytics\Operational\Aggregates\Hall\HallUtilizationRate as HallUtilizationRateAggregate;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;

class HallUtilizationRate
{
    protected HallUtilizationRateAggregate $hallUtilizationRateAggregate;
    public function __construct(HallUtilizationRateAggregate $hallUtilizationRateAggregate)
    {
        $this->hallUtilizationRateAggregate = $hallUtilizationRateAggregate;
    }
    public function getAllUtilizationRate($currentSchool)
    {
        $targetKpis = [
            OperationalAnalyticsKpi::HALL,
            OperationalAnalyticsKpi::ACTIVE_HALL,
        ];

        $query = OperationalAnalyticQuery::base($currentSchool->id, $targetKpis);
        return $this->hallUtilizationRateAggregate->calculate($query);
    }
}
