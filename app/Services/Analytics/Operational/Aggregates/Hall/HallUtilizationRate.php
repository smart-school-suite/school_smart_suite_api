<?php

namespace App\Services\Analytics\Operational\Aggregates\Hall;

use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
use Laravel\Scout\Builder;

class HallUtilizationRate
{
    public function calculate(Builder $query){
        $totalHalls = $query->where("kpi", OperationalAnalyticsKpi::HALL)->sum("value");
        $activeHalls = $query->where("kpi", OperationalAnalyticsKpi::ACTIVE_HALL)->sum("value");
        $utilizationRate = round($activeHalls / $totalHalls * 100, 2);
        return [
             "total_halls" => $totalHalls,
             "total_active_halls" => $activeHalls,
             "utilization_rate" => $utilizationRate
        ];
    }
}
