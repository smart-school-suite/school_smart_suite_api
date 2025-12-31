<?php

namespace App\Services\Analytics\Financial\Widget\ResitFee;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\ResitFee\ResitFeePaymentRateAggregate;
class ResitFeePaymentRate
{
    protected ResitFeePaymentRateAggregate $resitFeePaymentRateAggregate;
    public function __construct(ResitFeePaymentRateAggregate $resitFeePaymentRateAggregate)
    {
        $this->resitFeePaymentRateAggregate = $resitFeePaymentRateAggregate;
    }
    public function getResitFeePaymentRate($currentSchool, $year){
          $targetKpis = [
              FinancialAnalyticsKpi::RESIT_FEE_PAID,
              FinancialAnalyticsKpi::RESIT_FEE_INCURRED
          ];

          $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
          return $this->resitFeePaymentRateAggregate->calculate($query);
    }
}
