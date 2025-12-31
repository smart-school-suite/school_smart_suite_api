<?php

namespace App\Services\Analytics\Financial\Aggregate\RegistrationFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class RegistrationFeePaidAggregator
{
    public function calculate(Collection $query){
         return $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)
                   ->sum("value");
    }
}
