<?php

namespace App\Services\Analytics\Financial\Widget\ResitFee;

use App\Services\Analytics\Financial\Aggregate\ResitFee\ResitFeePaidVsDebtLevelExamTypeAggregate;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;

class ResitFeePaidVsDebtLevelExamType
{
    protected ResitFeePaidVsDebtLevelExamTypeAggregate $resitFeePaidVsDebtLevelExamTypeAggregate;
    public function __construct(ResitFeePaidVsDebtLevelExamTypeAggregate $resitFeePaidVsDebtLevelExamTypeAggregate)
    {
        $this->resitFeePaidVsDebtLevelExamTypeAggregate = $resitFeePaidVsDebtLevelExamTypeAggregate;
    }

    public function getResitFeePaidVsDebtLevelExamType($currentSchool, $year, $filters)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::RESIT_FEE_INCURRED,
            FinancialAnalyticsKpi::RESIT_FEE_PAID,
        ];

        $defaultFilters  = [
            "exam_type" => false,
            "level" => true
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $defaultFilters);
        if (empty($filters)) {
            return $this->resitFeePaidVsDebtLevelExamTypeAggregate->calculate($query, $targetKpis);
        }
        return $this->resitFeePaidVsDebtLevelExamTypeAggregate->calculate($query, $filters);
    }
}
