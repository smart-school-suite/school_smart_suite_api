<?php

namespace App\Services\Analytics\Financial\Aggregate\AdditionalFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class AdditionalFeeDebtVsPaidLevelAggregator
{
    public function calculate(Collection $query)
    {
        $levels = Educationlevels::all();
       return  $levels->map(function ($level)  use ($query) {
            $incurred = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED)
                ->where("level_id", $level->id)
                ->sum("value");
            $paid = $query->where("kpi", FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)
                ->where("level_id", $level->id)
                ->sum("value");
            return [
                "level_name" =>  $level->name ?? "unknown",
                "level" => $level->level ?? "unknown",
                "paid" => $paid ?? 0,
                "incurred" => $incurred ?? 0
            ];
        });
    }
}
