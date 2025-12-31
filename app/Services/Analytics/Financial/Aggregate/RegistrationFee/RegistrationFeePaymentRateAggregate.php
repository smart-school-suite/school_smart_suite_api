<?php

namespace App\Services\Analytics\Financial\Aggregate\RegistrationFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class RegistrationFeePaymentRateAggregate
{
    public function calculate(Collection $query)
    {
        $unPaidFee = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED)
            ->sum("value");
        $paidFee = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)
            ->sum("value");
        return [
            "unpaid_registration_fee" => $unPaidFee,
            "paid_registration_fee" => $paidFee,
            "payment_rate" => round($unPaidFee / $paidFee * 100, 2)
        ];
    }
}
