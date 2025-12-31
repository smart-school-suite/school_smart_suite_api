<?php

namespace App\Services\Analytics\Financial\Widget\SchoolExpenses;

use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Services\Analytics\Financial\Query\FinancialAnalyticQuery;
use App\Services\Analytics\Financial\Aggregate\SchoolExpense\SchoolExpenseCategoryAggregate;

class SchoolExpenseCategory
{
    protected SchoolExpenseCategoryAggregate $schoolExpenseCategoryAggregate;
    public function __construct(SchoolExpenseCategoryAggregate $schoolExpenseCategoryAggregate)
    {
        $this->schoolExpenseCategoryAggregate = $schoolExpenseCategoryAggregate;
    }
    public function getSchoolExpenseCategory($currentSchool, $year)
    {
        $targetKpis = [
            FinancialAnalyticsKpi::EXPENSE_INCURRED
        ];
        $query = FinancialAnalyticQuery::base($currentSchool->id, $year, $targetKpis);
        return $this->schoolExpenseCategoryAggregate->calculate($query, $currentSchool->id);
    }
}
