<?php

namespace App\Services;

use App\Models\SchoolExpenses;
use App\Models\Feepayment;
use App\Models\Student;
use App\Models\Studentresit;

class FinancialReportAnalytics
{
    // Implement your logic here
    //most things that concern finances are the #resitPayments, #schoolFees, schoolExpenses

    public function getFinancialData($currentSchool)
    {

        $schoolExpenses = SchoolExpenses::where("school_branch_id", $currentSchool->id)->with("schoolexpensescategory")->get();
        $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)->with(['specialty'])->get();
        $students = Student::where("school_branch_id", $currentSchool->id)->get();
        $feePayments = Feepayment::where("school_branch_id", $currentSchool->id)->with(['student'])->get();
        $preparedData = $this->prepareFinancialData(
            $schoolExpenses,
            $students,
            $currentSchool,
            $studentResits,
            $feePayments
        );

        return $preparedData;
    }

    private function prepareFinancialData($schoolExpenses, $students, $currentSchool, $studentResits, $feePayments)
    {
        $financialData = [];
        $totalIncome = $feePayments->sum('amount');
        $totalResitFees = $studentResits->sum('resit_fees');
        $totalExpenses = $schoolExpenses->sum('amount');
        $totalExpectedFees = $students->sum('total_fee_debt');
        $financialData['total_students'] = $students->count();
        $financialData['percentage_fees_paid'] = $this->calculatePercentageFeesPaid($students, $feePayments);
        $financialData['school_revenue_source'] = $this->getSchoolRevenueSource($feePayments, $currentSchool);
        $financialData['total_resit_fees_over_time'] = $this->getResitFeesOverTime($studentResits);
        $financialData['school_expenses'] = $this->groupExpensesByCategory($schoolExpenses);
        $netProfit = ($totalResitFees + $totalIncome) - $totalExpenses;
        $financialData['total_expenses'] = $totalExpenses;
        $financialData['net_profit'] = $netProfit;
        $financialData['expected_fees'] = $totalExpectedFees;
        $financialData['school_expenses_history']  = $this->getExpenseHistory($schoolExpenses);
        $financialData['enrollment_numbers'] = [
            'total_enrollment' => $students->count(),
            'new_enrollment' => $students->where('created_at', '>=', now()->subMonth())->count(),
            'withdrawals' => 50,
            'year_over_year_growth' => $this->getYearOverYearGrowth($students),
        ];
        $financialData['school_expenses'] = [
            'total_expenses' => $totalExpenses,
        ];
        return $financialData;
    }



    private function getYearOverYearGrowth($students)
    {
        $currentYearEnrollment = $students->where('created_at', '>=', now()->startOfYear())->count();
        $previousYearEnrollment = $students->whereBetween('created_at', [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()])->count();

        if ($previousYearEnrollment > 0) {
            return number_format((($currentYearEnrollment - $previousYearEnrollment) / $previousYearEnrollment) * 100, 2);
        }
        return 0;
    }


    private function getExpenseHistory($schoolExpenses)
    {
        $history = [];
        $currentYear = now()->year;

        for ($yearOffset = 0; $yearOffset < 5; $yearOffset++) {
            $year = $currentYear - $yearOffset;
            $totalExpenses = $schoolExpenses
                ->filter(function ($expense) use ($year) {
                    return substr($expense->created_at, 0, 4) == $year; // Extract YYYY from created_at
                })
                ->sum('amount');

            $history[] = [
                'year' => $year,
                'expenses' => (float) $totalExpenses,
            ];
        }

        return array_reverse($history);
    }

    private function calculatePercentageFeesPaid($students, $feePayments)
    {
        $totalFeesDue = $students->sum('total_fee_debt');

        $totalFeesPaid = $feePayments->sum('amount');

        if ($totalFeesDue > 0) {
            return number_format(($totalFeesPaid / $totalFeesDue) * 100, 2);
        }
        return 0;
    }
    private function getSchoolRevenueSource($feePayments, $studentResits)
    {
        $tuitionFees = $feePayments->sum('fee_amount');
        $studentResitsTotal = $studentResits->sum('resit_fee');

        $revenueSources = [
            "tuition_fees" => number_format($tuitionFees, 2),
            "student_resit" => number_format($studentResitsTotal, 2),
        ];

        return $revenueSources;
    }
    private function getResitFeesOverTime($studentResits)
    {
        $resitFeesOverTime = [];
        $currentYear = now()->format('Y');
        $resitFeesOverTime = [];
        for ($i = 1; $i <= 5; $i++) {
            $yearOverTime = $currentYear - $i;
            $totalFees = 0;
            foreach ($studentResits as $resit) {
                if ($resit->created_at && substr($resit->created_at, 0, 4) == $yearOverTime) {
                    $totalFees += $resit->resit_fee;
                }
            }

            $resitFeesOverTime[] = [
                'year' => $yearOverTime,
                'total_fees' => number_format($totalFees, 2),
            ];
        }

        return $resitFeesOverTime;
    }


    private function groupExpensesByCategory($schoolExpenses)
    {
        $groupedExpenses = $schoolExpenses->groupBy(function ($expense) {
            return optional($expense->schoolexpensescategory)->name;  // Handles potential null values
        })
            ->map(function ($group) {
                return [
                    'name' => $group->first()->schoolexpensescategory->name,
                    'amount' => $group->sum('amount'),
                ];
            })
            ->filter(function ($item) {
                return $item['name'] !== null; // Skip if category is null
            });

        return $groupedExpenses;
    }
}
