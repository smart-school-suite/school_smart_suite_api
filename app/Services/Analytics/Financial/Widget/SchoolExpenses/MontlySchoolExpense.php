<?php

namespace App\Services\Analytics\Financial\Widget\SchoolExpenses;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\SchoolExpense\MonthlySchoolExpenseAggregate;

class MontlySchoolExpense
{
    protected MonthlySchoolExpenseAggregate $monthlySchoolExpenseAggregate;
    public function __construct(MonthlySchoolExpenseAggregate $monthlySchoolExpenseAggregate)
    {
        $this->monthlySchoolExpenseAggregate = $monthlySchoolExpenseAggregate;
    }
    public function getMonthlySchoolExpense($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::EXPENSE_INCURRED
        ];
        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->monthlySchoolExpenseAggregate->calculate($query);
    }
}
