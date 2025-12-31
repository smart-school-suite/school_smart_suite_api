<?php

namespace App\Services\Analytics\Financial\Widget\AdditionalFee;
use App\Services\Analytics\Financial\Aggregate\AdditionalFee\AdditionalFeePaymentRateAggregate;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
class AdditionalFeePaymentRate
{
    protected AdditionalFeePaymentRateAggregate $additionalFeePaymentRateAggregate;
    public function __construct(AdditionalFeePaymentRateAggregate $additionalFeePaymentRateAggregate)
    {
        $this->additionalFeePaymentRateAggregate = $additionalFeePaymentRateAggregate;
    }

    public function getAdditionalFeePaymentRate($currentSchool, $year){
         $targetKpis = [
            FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED,
            FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID
         ];

         $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);

         return $this->additionalFeePaymentRateAggregate->calculate($query);
    }
}
