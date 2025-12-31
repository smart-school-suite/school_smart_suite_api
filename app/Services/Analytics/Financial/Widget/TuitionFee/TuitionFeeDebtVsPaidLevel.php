<?php

namespace App\Services\Analytics\Financial\Widget\TuitionFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\TuitionFee\TuitionFeePaidVsDebtLevel;

class TuitionFeeDebtVsPaidLevel
{
    protected TuitionFeePaidVsDebtLevel $tuitionFeePaidVsDebtLevel;
    public function __construct(TuitionFeePaidVsDebtLevel $tuitionFeePaidVsDebtLevel)
    {
        $this->tuitionFeePaidVsDebtLevel = $tuitionFeePaidVsDebtLevel;
    }
    public function getTuitionFeeDebtVsPaidLevel($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::TUITION_FEE_INCURRED
        ];

        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->tuitionFeePaidVsDebtLevel->calculate($query);
    }
}
