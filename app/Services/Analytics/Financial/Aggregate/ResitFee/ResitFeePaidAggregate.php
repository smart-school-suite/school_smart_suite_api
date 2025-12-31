<?php

namespace App\Services\Analytics\Financial\Aggregate\ResitFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Database\Eloquent\Collection;

class ResitFeePaidAggregate
{
    public function calculate(Collection $query){
         return $query->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)
                 ->sum("value");
    }
}
