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
        $query = OperationalAnalyticQuery::base($currentSchool, $targetKpis);
        return [
           "total_department" => $query->where("kpi", OperationalAnalyticsKpi::SPECIALTY)->value,
           "total_specialty" => $query->where("kpi", OperationalAnalyticsKpi::HALL)->value,
           "total_hall" => $query->where("kpi", OperationalAnalyticsKpi::HALL)->value
        ];
    }
}
