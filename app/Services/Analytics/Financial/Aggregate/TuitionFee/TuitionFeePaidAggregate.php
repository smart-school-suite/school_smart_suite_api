<?php

namespace App\Services\Analytics\Financial\Aggregate\TuitionFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class TuitionFeePaidAggregate
{
   public function calculate(Collection $query){
      return $query->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_PAID)
                   ->sum("value");
   }
}
