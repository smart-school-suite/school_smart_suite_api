<?php

namespace App\Services\Analytics\Financial\Widget\TuitionFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\TuitionFee\TuitionFeePaymentRateAggregate;

class TuitionFeePaymentRate
{
    protected TuitionFeePaymentRateAggregate $tuitionFeePaymentRateAggregate;
    public function __construct(TuitionFeePaymentRateAggregate $tuitionFeePaymentRateAggregate)
    {
        $this->tuitionFeePaymentRateAggregate = $tuitionFeePaymentRateAggregate;
    }
    public function getTuitionFeePaymentRate($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::TUITION_FEE_PAID,
            FinancialAnalyticsKpi::TUITION_FEE_INCURRED,
        ];
        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return  $this->tuitionFeePaymentRateAggregate->calculate($query);
    }
}
