<?php

namespace App\Services\Analytics\Financial\Aggregate\ResitFee;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Models\Educationlevels;
use App\Models\Examtype;
use Illuminate\Support\Collection;

class ResitFeePaidVsDebtLevelExamTypeAggregate
{
    public function calculate(Collection $query, $filter)
    {
        if ($filter['exam_type']) {
            return $this->byExamType($query);
        }
        if ($filter['level']) {
            return $this->byLevelType($query);
        }
    }

    protected function byExamType(Collection $query)
    {
        $examTypes = Examtype::where("type", "!=", "resit")
            ->where("type", "!=", "ca")
            ->get();
       return  $examTypes->map(function ($examType) use ($query) {
            $unpaidResitFee =   $query->where("exam_type_id", $examType->id)
                ->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_INCURRED)
                ->sum("value");
            $paidResitFee = $query->where("exam_type_id", $examType->id)
                ->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)
                ->sum("value");

            return [
                "exam_type_id" => $examType->id,
                "exam_name" => $examType->exam_name ?? "unknown",
                "unpaid_resit_fee" => $unpaidResitFee,
                "paid_resit_fee" => $paidResitFee,
                "payment_rate" => round($paidResitFee / $unpaidResitFee * 100, 2)
            ];
        });
    }

    protected function byLevelType(Collection $query)
    {
        $levels = Educationlevels::all();
       return  $levels->map(function ($level) use ($query) {
            $unpaidResitFee =   $query->where("exam_type_id", $level->id)
                ->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_INCURRED)
                ->sum("value");
            $paidResitFee = $query->where("exam_type_id", $level->id)
                ->where("kpi", FinancialAnalyticsKpi::RESIT_FEE_PAID)
                ->sum("value");
            return [
                "level_id" => $level->id,
                "level_name" => $level->name ?? "unknown",
                "unpaid_resit_fee" => $unpaidResitFee,
                "paid_resit_fee" => $paidResitFee,
                "payment_rate" => round($paidResitFee / $unpaidResitFee * 100, 2)
            ];
        });
    }
}
