<?php

namespace App\Services\Analytics\Financial\Widget\AdditionalFee;
use App\Services\Analytics\Financial\Aggregate\AdditionalFee\AdditionalFeePaidVsUnpaidCategoryAggregate;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
class AdditionalFeePaidVsUnpaidCategory
{
    protected AdditionalFeePaidVsUnpaidCategoryAggregate $additionalFeePaidVsUnpaidCategoryAggregate;
    public function __construct(AdditionalFeePaidVsUnpaidCategoryAggregate $additionalFeePaidVsUnpaidCategoryAggregate)
    {
        $this->additionalFeePaidVsUnpaidCategoryAggregate = $additionalFeePaidVsUnpaidCategoryAggregate;
    }

    public function getAdditionalFeePaidVsUnpaidCategory($currentSchool, $year){
         $targetKpis = [
             FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED,
             FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID
         ];

         $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
         return $this->additionalFeePaidVsUnpaidCategoryAggregate->calculate($query, $currentSchool->id);
    }
}
