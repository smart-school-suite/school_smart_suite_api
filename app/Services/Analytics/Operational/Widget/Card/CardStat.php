<?php

namespace App\Services\Analytics\Operational\Widget\Card;

use App\Services\Analytics\Operational\Query\OperationalAnalyticQuery;
use App\Constant\Analytics\Operational\OperationalAnalyticsKpi;
class CardStat
{
    public function getCardStats($currentSchool){
        $targetKpis = [
            OperationalAnalyticsKpi::SPECIALTY,
            OperationalAnalyticsKpi::HALL,
            OperationalAnalyticsKpi::DEPARTMENT
        ];
        $query = OperationalAnalyticQuery::base($currentSchool->id, $targetKpis);
        return [
           "total_department" => $query->where("kpi", OperationalAnalyticsKpi::DEPARTMENT)->first()->value ?? 0,
           "total_specialty" => $query->where("kpi", OperationalAnalyticsKpi::SPECIALTY)->first()->value ?? 0,
           "total_hall" => $query->where("kpi", OperationalAnalyticsKpi::HALL)->first()->value ?? 0
        ];
    }
}
