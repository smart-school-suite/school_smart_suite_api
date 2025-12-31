<?php

namespace App\Services\Analytics\Financial\Aggregate\AdditionalFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class AdditionalFeePaidAggregate
{
    public function calculate(Collection $query)
    {
        $additionalFeePaid = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)
            ->sum("value");
        return $additionalFeePaid ?? 0;
    }
}
