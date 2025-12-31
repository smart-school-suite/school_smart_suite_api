<?php

namespace App\Services\Analytics\Financial\Aggregate\AdditionalFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class AdditionalFeePaymentRateAggregate
{
    public function calculate(Collection $query){
         $additionalFeePaid = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)
                                     ->sum("value");
         $additionalFeeIncurred = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED)
                                    ->sum("value");
         $paymentRate = round($additionalFeePaid / $additionalFeeIncurred * 100, 2);
         return [
             "additional_fee_paid" => $additionalFeePaid,
             "additional_fee_incurred" => $additionalFeeIncurred,
             "payment_rate" => $paymentRate
         ];
    }
}
