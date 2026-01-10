<?php

namespace App\Services\Analytics\Financial\Aggregate\TuitionFee;

use App\Models\Educationlevels;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use Illuminate\Support\Collection;

class TuitionFeePaidVsDebtLevel
{
    public function calculate(Collection $query)
    {
        $levels = Educationlevels::all();

        return $levels->map(function ($level) use ($query) {
            $levelData = $query->where("level_id", $level->id);

            $unpaid = $levelData->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_INCURRED)->sum("value");
            $paid = $levelData->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_PAID)->sum("value");

            return [
                "tuition_fee_paid" => $paid,
                "tuition_fee_unpaid" => $unpaid,
                "level_id" => $level->id,
                "level_name" => $level->name,
                "level" => $level->level,
                "payment_rate" => $unpaid > 0 ? round(($paid / $unpaid) * 100, 2) : 0,
            ];
        });
    }
}
