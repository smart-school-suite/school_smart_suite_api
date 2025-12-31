<?php

namespace App\Services\Analytics\Financial\Aggregate\ResitFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Database\Eloquent\Collection;

class ResitFeePaymentRateAggregate
{
    public function calculate(Collection $query)
    {
        $resitFeeIncurred = $query->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_INCURRED)
            ->sum("value");
        $resitFeePaid = $query->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)
            ->sum("value");
        return [
            "payment_rate" => round($resitFeePaid / $resitFeeIncurred * 100, 2),
            "resit_fee_paid" => $resitFeePaid,
            "resit_fee_incurred" => $resitFeeIncurred
        ];
    }
}
