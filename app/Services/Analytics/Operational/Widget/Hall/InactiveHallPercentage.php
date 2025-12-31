<?php

namespace App\Services\Analytics\Operational\Widget\Hall;

use App\Services\Analytics\Operational\Aggregates\Hall\InactiveHallPercentage as InactiveHallPercentageAggregate;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;

class InactiveHallPercentage
{
    protected InactiveHallPercentageAggregate $inactiveHallPercentageAggregate;
    public function __construct(InactiveHallPercentageAggregate $inactiveHallPercentageAggregate)
    {
        $this->inactiveHallPercentageAggregate = $inactiveHallPercentageAggregate;
    }
    public function getActiveHallPercentage($currentSchool)
    {
        $targetKpis = [
            OperationalAnalyticsKpi::HALL,
            OperationalAnalyticsKpi::INACTIVE_HALL,
        ];

        $query = OperationalAnalyticQuery::base($currentSchool->id, $targetKpis);
        return $this->inactiveHallPercentageAggregate->calculate($query);
    }
}
