<?php

namespace App\Services\Analytics\Financial\Aggregate\TuitionFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class TuitionFeePaymentRateAggregate
{
    public function calculate(Collection $query)
    {
        $unPaidFee = $query->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_INCURRED)
            ->sum("value");
        $paidFee = $query->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_PAID)
            ->sum("value");
        return [
            "unpaid_tuition_fee" => $unPaidFee,
            "paid_tuition_fee" => $paidFee,
            "payment_rate" => round($unPaidFee / $paidFee * 100, 2)
        ];
    }
}
