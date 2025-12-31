<?php

namespace App\Services\Analytics\Operational\Aggregates\Hall;

use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use Laravel\Scout\Builder;

class InactiveHallPercentage
{
    public function calculate(Builder $query)
    {
        $totalHalls = $query->where("kpi", OperationalAnalyticsKpi::HALL)->sum("value");
        $inactiveHalls = $query->where("kpi", OperationalAnalyticsKpi::INACTIVE_HALL)->sum("value");
        $inactiveHallPercentage = round($inactiveHalls / $totalHalls * 100, 2);
        return [
            "total_halls" => $totalHalls,
            "total_inactive_halls" => $inactiveHalls,
            "inactive_hall_percentage" => $inactiveHallPercentage
        ];
    }
}
