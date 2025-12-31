<?php

namespace App\Services\Analytics\Financial\Aggregate\Revenue;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolRevenueAggregate
{
    public function calculate(Collection $query)
    {
        $tutionFeePaid = $query->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_PAID)->sum("value");
        $additionalFeePaid = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)->sum("value");
        $registrationFeePaid = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)->sum("value");
        $schoolExpense  = $query->where("kpi", FinancialAnalyticsKpi::EXPENSE_INCURRED)->sum("value");
        $resitFeePaid = $query->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)->sum("value");
        return  [
            "revenue" => $tutionFeePaid ?? 0 + $additionalFeePaid ?? 0 + $registrationFeePaid ?? 0 + $resitFeePaid ?? 0
             - $schoolExpense,
        ];
    }
}
