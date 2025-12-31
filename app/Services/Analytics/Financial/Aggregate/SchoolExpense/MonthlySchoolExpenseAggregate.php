<?php

namespace App\Services\Analytics\Financial\Aggregate\SchoolExpense;
use Carbon\Carbon;
use Illuminate\Support\Collection;
class MonthlySchoolExpenseAggregate
{
    public function calculate(Collection $query)
    {
        $monthsOfYear = $this->getMonthsOfYear();
        $monthsOfYear->map(function ($month) use ($query) {
            $expenseTotal = $query->where("month", $month->month_count)->sum("value");
            return [
                "month_short" => $month->month_short,
                "month_full" => $month->month_full,
                "month_count" => $month->month_count,
                "expense_total" => $expenseTotal
            ];
        });
    }

    protected function getMonthsOfYear(): Collection
    {
        return collect(range(1, 12))->map(function ($month) {
            $date = Carbon::create(null, $month, 1);

            return [
                'month_short' => strtolower($date->format('M')),
                'month_full'  => strtolower($date->format('F')),
                'month_count' => $month,
            ];
        });
    }
}
