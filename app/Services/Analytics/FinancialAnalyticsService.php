<?php

namespace App\Services\Analytics;

use App\Models\Analytics\Finance\FinanceAnalyticSnapshot;
use App\Models\Analytics\Finance\FinanceAnalyticTimeSeries;
use App\Constant\Analytics\Financial\FinancialAnalyticsKpi;
use App\Models\Schoolexpensescategory;
use App\Models\AdditionalFeesCategory;
use App\Models\Department;

class FinancialAnalyticsService
{
    public function getFinanceAnalyticsStats($currentSchool, $year)
    {
        return [
            'school_revenue' => self::getSchoolRevenue($currentSchool, $year),
            'total_school_expenses' => self::getTotalSchoolExpenses($currentSchool, $year),
            'monthly_expenses' => self::getMonthlyExpenses($currentSchool, $year),
            'expense_by_category' => self::getExpenseByCategory($currentSchool, $year),
            'additional_fee_paid' => self::getAdditionalFeePaid($currentSchool, $year),
            'additional_fee_by_category' => self::getAdditionalFeeCategory($currentSchool, $year),
            'additional_fee_debt' => self::getAdditionalFeeDebt($currentSchool, $year),
            'registration_fee_paid' => self::getRegistrationFeePaid($currentSchool, $year),
            'registration_fee_debt' => self::getRegistrationFeeDebt($currentSchool, $year),
            'tuition_fee_paid' => self::getTuitionFeePaid($currentSchool, $year),
            'tuition_fee_debt' => self::getTuitionFeeDebt($currentSchool, $year),
            'tuition_fee_breakdown' => self::tuitionFeeBreakDown($currentSchool, $year),
            'resit_fee_paid' => self::getResitFeePaid($currentSchool, $year),
            'resit_fee_debt' => self::getResitFeeDebt($currentSchool, $year),
        ];
    }

    protected static function getTotalSchoolExpenses($currentSchool, $year)
    {
        $expenses =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::EXPENSE_INCURRED)
            ->where('year', $year)
            ->first();
        return $expenses->value ?? 0;
    }
    protected static function getMonthlyExpenses($currentSchool, $year)
    {

        $montlyExpenses = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::MONTH_EXPENSE_INCURRED)
            ->where('year', $year)
            ->get();
        if ($montlyExpenses->isEmpty()) {
            return [];
        }

        return   $montlyExpenses->map(function ($expense) {
            return [
                'month' => $expense->month,
                'amount' => $expense->value
            ];
        });
    }
    protected static function getExpenseByCategory($currentSchool, $year)
    {
        $expenseByCategory =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::EXPENSE_CATEGORY_INCURRED)
            ->where('year', $year)
            ->get();
        if ($expenseByCategory->isEmpty()) {
            return [];
        }
        return $expenseByCategory->map(function ($expense) {
            return [
                'category_id' => Schoolexpensescategory::find($expense->category_id)->name ?? 'Unknown',
                'amount' => $expense->value
            ];
        });
    }
    protected static function getAdditionalFeePaid($currentSchool, $year)
    {
        $additionalFeePaid = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID)
            ->where('year', $year)
            ->first();

        return $additionalFeePaid->value ?? 0;
    }
    protected static function getAdditionalFeeCategory($currentSchool, $year)
    {
        $additionalFeeByCategory =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->whereIn('kpi', [
                FinancialAnalyticsKpi::ADDITIONAL_FEE_PAID_CATEGORY,
            ])
            ->where('year', $year)
            ->get();
        if ($additionalFeeByCategory->isEmpty()) {
            return [];
        }
        return $additionalFeeByCategory->map(function ($additionalFee) {
            return [
                'category_id' => AdditionalFeesCategory::find($additionalFee->category_id)->title ?? 'Unknown',
                'amount' => $additionalFee->value
            ];
        });
    }
    protected static function getAdditionalFeeDebt($currentSchool, $year)
    {
        $additionalFeeDebt =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::ADDITIONAL_FEE_INCURRED)
            ->where('year', $year)
            ->first();
        return $additionalFeeDebt->value ?? 0;
    }
    protected static function getRegistrationFeePaid($currentSchool, $year)
    {
        $registrationFeePaid = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::REGISTRATION_FEE_PAID)
            ->where('year', $year)
            ->first();
        return $registrationFeePaid->value ?? 0;
    }
    protected static function getRegistrationFeeDebt($currentSchool, $year)
    {
        $registrationFeeDebt = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::REGISTRATION_FEE_INCURRED)
            ->where('year', $year)
            ->first();
        return $registrationFeeDebt->value ?? 0;
    }
    protected static function getTuitionFeePaid($currentSchool, $year)
    {
        $tuitionFeePaid =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::TUITION_FEE_PAID)
            ->where('year', $year)
            ->first();
        return $tuitionFeePaid->value ?? 0;
    }
    protected static function getTuitionFeeDebt($currentSchool, $year)
    {
        $tuitionFeeDebt = FinanceAnalyticSnapshot::where("school_branch_id", $currentSchool->id)
            ->where("kpi", FinancialAnalyticsKpi::TUITION_FEE_INCURRED)
            ->where('year', $year)
            ->first();
        return $tuitionFeeDebt->value  ?? 0;
    }
    protected static function tuitionFeeBreakDown($currentSchool, int $year): array
    {
        $topDebtDepartments = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::TUITION_FEE_INCURRED_DEPARTMENT)
            ->where('year', $year)
            ->whereNotNull('department_id')
            ->orderByDesc('value')
            ->limit(5)
            ->pluck('value', 'department_id')
            ->toArray();

        if (empty($topDebtDepartments)) {
            return [];
        }

        $departmentIds = array_keys($topDebtDepartments);

        $paidAmounts = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::TUITION_FEE_PAID_DEPARTMENT)
            ->where('year', $year)
            ->whereIn('department_id', $departmentIds)
            ->pluck('value', 'department_id')
            ->toArray();

        $breakdown = [];

        foreach ($topDebtDepartments as $departmentId => $incurred) {
            $paid = $paidAmounts[$departmentId] ?? 0;
            $outstanding = $incurred - $paid;
            $percentagePaid = $incurred > 0 ? round(($paid / $incurred) * 100, 2) : 0;

            $breakdown[] = [
                'department'     => Department::find($departmentId)->department_name ?? 'Unknown',
                'total_incurred'    => $incurred,
                'total_paid'        => $paid,
                'amount_left'       => $outstanding,
                'percentage_paid'   => $percentagePaid,
                'percentage_outstanding' => round(100 - $percentagePaid, 2),
            ];
        }

        usort($breakdown, fn($a, $b) => $b['total_incurred'] <=> $a['total_incurred']);

        return $breakdown;
    }
    protected static function getResitFeePaid($currentSchool, $year)
    {
        $resitFeePaid =  FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::RESIT_FEE_PAID)
            ->where('year', $year)
            ->first();
        return $resitFeePaid->value ?? 0;
    }
    protected static function getResitFeeDebt($currentSchool, $year)
    {
        $resitFeeDebt = FinanceAnalyticSnapshot::where('school_branch_id', $currentSchool->id)
            ->where('kpi', FinancialAnalyticsKpi::RESIT_FEE_INCURRED)
            ->where('year', $year)
            ->first();
        return $resitFeeDebt->value ?? 0;
    }
    protected static function getSchoolRevenue($currentSchool, $year)
    {
        $resitFee = Self::getResitFeePaid($currentSchool, $year);
        $tuitionFee = Self::getTuitionFeePaid($currentSchool, $year);
        $registrationFee = Self::getRegistrationFeePaid($currentSchool, $year);
        $additionalFee = Self::getAdditionalFeePaid($currentSchool, $year);
        $schoolExpense = Self::getTotalSchoolExpenses($currentSchool, $year);
        return $resitFee + $tuitionFee + $registrationFee + $additionalFee - $schoolExpense;
    }
}
