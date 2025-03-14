<?php

namespace App\Services\Stats;

use App\Models\AdditionalFees;
use App\Models\RegistrationFee;
use App\Models\SchoolExpenses;
use App\Models\Feepayment;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\TuitionFees;

class FinancialStatistics
{
    // Implement your logic here
    public function getFinancialData($currentSchool)
    {

        $schoolExpenses = SchoolExpenses::where("school_branch_id", $currentSchool->id)->with("schoolexpensescategory")->get();
        $studentResits = Studentresit::where("school_branch_id", $currentSchool->id)->with(['specialty'])->get();
        $students = Student::where("school_branch_id", $currentSchool->id)->get();
        $feePayments = Feepayment::where("school_branch_id", $currentSchool->id)->with(['student'])->get();
        $registrationFees = RegistrationFee::where("school_branch_id", $currentSchool->id)->get();
        $tuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)->get();
        $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->get();
        $preparedData = $this->prepareFinancialData(
            $schoolExpenses,
            $students,
            $studentResits,
            $feePayments,
            $registrationFees,
            $tuitionFees,
            $additionalFees,
        );

        return $preparedData;
    }

    private function prepareFinancialData($schoolExpenses, $students,  $studentResits, $feePayments, $registrationFees,
    $tuitionFees, $additionalFees)
    {
        $financialData = [];
        $totalExpenses = $schoolExpenses->sum('amount');
        $totalExpectedFees = $students->sum('total_fee_debt');
        $financialData['total_students'] = $students->count();
        $financialData['percentage_fees_paid'] = $this->calculatePercentageFeesPaid($students, $feePayments);
        $financialData['school_revenue_source'] = $this->getSchoolRevenueSource( $studentResits, $registrationFees,
        $tuitionFees, $additionalFees);
        $financialData['total_resit_fees_over_time'] = $this->getResitFeesOverTime($studentResits);
        $financialData['school_expenses'] = $this->groupExpensesByCategory($schoolExpenses);
        $financialData['total_expenses'] = $totalExpenses;
        $financialData['net_profit'] = $this->getNetProfit($studentResits, $registrationFees, $tuitionFees, $additionalFees, $schoolExpenses);
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



    private function getNetProfit($studentResits, $registrationFees,
    $tuitionFees, $additionalFees, $schoolExpenses){
        $studentResitsTotal = $studentResits->sum('resit_fee');
        $registrationFees = $registrationFees->sum('amount');
        $tuitionFees = $tuitionFees->sum('tution_fee_total');
        $additionalFees = $additionalFees->sum('amount');
        $totalSchoolExpenses = $schoolExpenses->sum("amount");
        $netProfit = ($studentResitsTotal + $registrationFees + $tuitionFees + $additionalFees) - $totalSchoolExpenses;
        return $netProfit;
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
    private function getSchoolRevenueSource($studentResits, $registrationFees,
    $tuitionFees, $additionalFees)
    {
        $studentResitsTotal = $studentResits->sum('resit_fee');
        $registrationFees = $registrationFees->sum('amount');
        $tuitionFees = $tuitionFees->sum('tution_fee_total');
        $additionalFees = $additionalFees->sum('amount');
        $revenueSources = [
            "tuition_fees" => number_format($tuitionFees, 2),
            "student_resit" => number_format($studentResitsTotal, 2),
            "additional_fees" => number_format($additionalFees, 2),
            "registration_fees" => number_format($registrationFees, 2)
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
