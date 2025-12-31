<?php

namespace App\Services\Analytics\Financial\Aggregate\SchoolExpense;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class SchoolExpenseAggregate
{
    public function calculate(Collection $query){
         return $query->where("kpi", FinancialAnalyticsKpi::EXPENSE_INCURRED)
         ->sum("value");
    }
}
