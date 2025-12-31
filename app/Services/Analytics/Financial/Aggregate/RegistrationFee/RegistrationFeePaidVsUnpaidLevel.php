<?php

namespace App\Services\Analytics\Financial\Aggregate\RegistrationFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Models\Educationlevels;
use Illuminate\Support\Collection;

class RegistrationFeePaidVsUnpaidLevel
{
    public function calculate(Collection $query){
         $levels = Educationlevels::all();
        return  $levels->map(function ($level) use ($query) {
               $unpaid = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED)
                            ->where("level_id", $level->id)
                            ->sum("value");
               $paid = $query->where("kpi", FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)
                          ->where("level_id", $level->id)
                            ->sum("value");
                return  [
                     "registration_fee_paid" => $paid ?? 0,
                     "registraiton_fee_unpaid" => $unpaid ?? 0,
                     "level_id" => $level->id,
                     "level_name" => $level->name,
                     "level" => $level->level,
                     "payment_rate" => round($unpaid / $paid * 100, 2),
                ];
         });
    }
}
