<?php

namespace App\Services\Analytics\Financial\Aggregate\Revenue;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolRevenueSourceAggregate
{
    public function calculate(Collection $query)
    {
        $tuitionFeePaid = $query->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_PAID)->sum("value");
        $additionalFeePaid = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)->sum("value");
        $registrationFeePaid = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)->sum("value");
        $resitFeePaid = $query->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)->sum("value");
        return  [
            "tuition_fee" => $tuitionFeePaid,
            "additional_fee_paid" => $additionalFeePaid,
            "registration_fee_paid" => $registrationFeePaid,
            "resit_fee_paid" => $resitFeePaid
        ];
    }
}
