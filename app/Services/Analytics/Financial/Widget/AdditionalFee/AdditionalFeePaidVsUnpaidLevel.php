<?php

namespace App\Services\Analytics\Financial\Widget\AdditionalFee;
use App\Services\Analytics\Financial\Aggregate\AdditionalFee\AdditionalFeeDebtVsPaidLevelAggregator;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
class AdditionalFeePaidVsUnpaidLevel
{
    protected AdditionalFeeDebtVsPaidLevelAggregator $additionalFeeDebtVsPaidLevelAggregator;
    public function __construct(AdditionalFeeDebtVsPaidLevelAggregator $additionalFeeDebtVsPaidLevelAggregator)
    {
        $this->additionalFeeDebtVsPaidLevelAggregator = $additionalFeeDebtVsPaidLevelAggregator;
    }

    public function getAdditionalFeeDebtVsPaidLevel($currentSchool, $year){
         $targetKpis = [
             FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED,
             FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID
         ];
         $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
         return $this->additionalFeeDebtVsPaidLevelAggregator->calculate($query);
    }
}
