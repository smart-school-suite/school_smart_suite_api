<?php

namespace App\Services\Stats;

use App\Models\AdditionalFeesCategory;
use App\Models\Schoolexpensescategory;
use Illuminate\Support\Facades\DB;
use App\Models\StatTypes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FinancialStatService
{
    /**
     * Array mapping month numbers to their abbreviations.
     * @var array
     */
    private array $monthNames = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
        7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    ];

    /**
     * Retrieves various financial statistics for a given school branch.
     *
     * @param object $currentSchool The current school branch object.
     * @param int|null $year The year for which to retrieve statistics (defaults to current year).
     * @param int|null $month The month for which to retrieve statistics (defaults to current month).
     * @return array An associative array of financial statistics.
     */
    public function getFinancialStats($currentSchool, $year = null, $month = null): array
    {
        // Use current year/month if not provided
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        // Define target KPIs to fetch from StatTypes
        $targetKpis = [
            'total_additional_fee',
            'resit_fee_total_amount_paid',
            'registration_fee_total_amount_paid',
            'registered_students_count_over_time',
            'total_tuition_fee_amount_paid',
            'total_tuition_fee_debt',
            'school_expenses_progress',
            'total_revenue_progress'
        ];

        // Fetch all necessary KPI IDs in a single query and key them by program_name
        $kpis = StatTypes::whereIn('program_name', $targetKpis)->get()->keyBy('program_name');

        // Prepare KPI IDs for direct, null-safe access
        $kpiIds = [
            'total_additional_fee'              => $kpis->get('total_additional_fee')->id ?? null,
            'resit_fee_total_amount_paid'       => $kpis->get('resit_fee_total_amount_paid')->id ?? null,
            'registration_fee_total_amount_paid' => $kpis->get('registration_fee_total_amount_paid')->id ?? null,
            'registered_students_count_over_time' => $kpis->get('registered_students_count_over_time')->id ?? null,
            'total_tuition_fee_amount_paid'     => $kpis->get('total_tuition_fee_amount_paid')->id ?? null,
            'school_expenses_progress'          => $kpis->get('school_expenses_progress')->id ?? null,
            'total_revenue_progress'            => $kpis->get('total_revenue_progress')->id ?? null
        ];

        // --- Fetch all raw data efficiently from the database ---
        $additionalFeeData = DB::table('additional_fees_trans_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('year', $year)
            ->when($kpiIds['total_additional_fee'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->get(['decimal_value', 'month', 'category_id']);

        $tuitionFeeData = DB::table('tuition_fees_trans_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('year', $year)
            ->when($kpiIds['total_tuition_fee_amount_paid'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->get(['decimal_value', 'month']);

        $registrationFeeData = DB::table('registration_fee_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year])
            ->when($kpiIds['registration_fee_total_amount_paid'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->get(['decimal_value', 'year']);

        $resitFeeData = DB::table('resit_fee_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year])
            ->when($kpiIds['resit_fee_total_amount_paid'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->get(['decimal_value', 'year']);

        $schoolExpensesData = DB::table('school_expenses_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->where('year', $year)
            ->get(['decimal_value', 'month', 'category_id']);

        // Data for the last 5 years, including the current year.
        $studentData = DB::table('student_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year])
            ->when($kpiIds['registered_students_count_over_time'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->get(['integer_value', 'year']);

        // --- Calculate current period's total sums to pass to progress methods ---
        $totalAdditionalFeesCurrentYear = (float) $additionalFeeData->sum('decimal_value');
        $totalTuitionFeesPaidCurrentYear = (float) $tuitionFeeData->sum('decimal_value');
        $totalRegistrationFeeCurrentYear = (float) $registrationFeeData->where('year', $year)->sum('decimal_value');
        $totalResitFeesCurrentYear = (float) $resitFeeData->where('year', $year)->sum('decimal_value');
        $totalSchoolExpensesCurrentYear = (float) $schoolExpensesData->sum('decimal_value');

        // --- Process the fetched data using the cleaner private methods ---
        return [
            'total_school_expenses' => $this->totalSchoolExpenses($schoolExpensesData),
            'school_expenses_over_time' => $this->schoolExpensesOverTime($schoolExpensesData),
            'school_expenses_by_category' => $this->schoolExpensesByCategory($schoolExpensesData),
            'total_additional_fees' => $this->totalAdditionalFees($additionalFeeData),
            'additional_fees_total_by_category' => $this->additionalFeesTotalByCategory($additionalFeeData),
            'total_additional_fees_over_time' => $this->totalAdditionalFeesOverTime($additionalFeeData),
            'total_resit_fees' => $this->totalResitFees($resitFeeData),
            'total_resit_fee_over_years' => $this->totalResitFeeOverYears($resitFeeData),
            'total_registration_fee' => $this->totalRegistrationFee($registrationFeeData),
            'total_registration_fee_over_years' => $this->totalRegistrationFeeOverYears($registrationFeeData),
            'total_tuition_fees_paid' => $this->totalTuitionFeesPaid($tuitionFeeData),
            'total_tuition_fees_paid_over_time' => $this->totalTuitionFeesPaidOverTime($tuitionFeeData),
            'school_revenue_source' => $this->schoolRevenueSource($additionalFeeData, $tuitionFeeData, $registrationFeeData, $resitFeeData),
            'total_student_registration_over_years' => $this->totalStudentRegistrationOverYears($studentData),
            'total_student_registration' => $this->totalStudentRegistration($studentData),

            // Calculate revenue and expenses progress using the refined methods
            'revenue_progress' => $this->calculateRevenueProgress(
                $totalTuitionFeesPaidCurrentYear,
                $totalRegistrationFeeCurrentYear,
                $totalAdditionalFeesCurrentYear,
                $totalResitFeesCurrentYear,
                $totalSchoolExpensesCurrentYear,
                $kpiIds['total_revenue_progress'],
                $currentSchool,
                $year,
                $month
            ),
            'school_expenses_progress' => $this->calculateSchoolExpenseProgress(
                $totalSchoolExpensesCurrentYear,
                $kpiIds['school_expenses_progress'],
                $currentSchool,
                $year,
                $month
            )
        ];
    }

    /**
     * Helper to format data over time (e.g., by month).
     *
     * @param Collection $data The collection of data with 'month' and 'decimal_value'.
     * @return array
     */
    private function formatDataOverTime(Collection $data): array
    {
        return $data->map(function ($item) {
            $monthName = $this->monthNames[$item->month] ?? 'Unknown';
            return [
                'month' => $monthName,
                'amount' => $item->decimal_value ?? 0.0,
            ];
        })->toArray();
    }

    /**
     * Calculates the total school expenses.
     *
     * @param Collection $schoolExpensesData
     * @return float
     */
    private function totalSchoolExpenses(Collection $schoolExpensesData): float
    {
        return (float) $schoolExpensesData->sum('decimal_value');
    }

    /**
     * Formats school expenses over time.
     *
     * @param Collection $schoolExpensesData
     * @return array
     */
    private function schoolExpensesOverTime(Collection $schoolExpensesData): array
    {
        return $this->formatDataOverTime($schoolExpensesData);
    }

    /**
     * Calculates school expenses broken down by category.
     * Fetches category names efficiently using whereIn.
     *
     * @param Collection $schoolExpensesData
     * @return Collection
     */
    private function schoolExpensesByCategory(Collection $schoolExpensesData): Collection
    {
        $categoryIds = $schoolExpensesData->pluck('category_id')->filter()->unique();
        $categories = Schoolexpensescategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        return $schoolExpensesData->groupBy('category_id')->map(function ($items, $categoryId) use ($categories) {
            $totalAmount = $items->sum('decimal_value');
            return [
                'category' => $categories->get($categoryId)->name ?? 'Unknown',
                'total_amount' => (float) $totalAmount,
            ];
        })->values();
    }

    /**
     * Calculates additional fees total broken down by category.
     * Fetches category names efficiently using whereIn.
     *
     * @param Collection $additionalFeeData
     * @return Collection
     */
    private function additionalFeesTotalByCategory(Collection $additionalFeeData): Collection
    {
        $categoryIds = $additionalFeeData->pluck('category_id')->filter()->unique();
        $categories = AdditionalFeesCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        return $additionalFeeData->groupBy('category_id')->map(function ($items, $categoryId) use ($categories) {
            $totalAmount = $items->sum('decimal_value');
            return [
                'category' => $categories->get($categoryId)->name ?? 'Unknown',
                'total_amount' => (float) $totalAmount,
            ];
        })->values();
    }

    /**
     * Calculates the total additional fees.
     *
     * @param Collection $additionalFeeData
     * @return float
     */
    private function totalAdditionalFees(Collection $additionalFeeData): float
    {
        return (float) $additionalFeeData->sum('decimal_value');
    }

    /**
     * Formats total additional fees over time.
     *
     * @param Collection $additionalFeeData
     * @return array
     */
    private function totalAdditionalFeesOverTime(Collection $additionalFeeData): array
    {
        return $this->formatDataOverTime($additionalFeeData);
    }

    /**
     * Calculates the total resit fees.
     *
     * @param Collection $resitFeeData
     * @return float
     */
    private function totalResitFees(Collection $resitFeeData): float
    {
        return (float) $resitFeeData->sum('decimal_value');
    }

    /**
     * Calculates total resit fees over several years.
     *
     * @param Collection $resitFeeData
     * @return Collection
     */
    private function totalResitFeeOverYears(Collection $resitFeeData): Collection
    {
        return $resitFeeData->groupBy('year')->map(function ($items, $year) {
            $totalAmount = $items->sum('decimal_value');
            return [
                'year' => (int) $year,
                'total_amount' => (float) $totalAmount,
            ];
        })->values()->sortBy('year')->values();
    }

    /**
     * Calculates the total registration fee.
     *
     * @param Collection $registrationFeeData
     * @return float
     */
    private function totalRegistrationFee(Collection $registrationFeeData): float
    {
        return (float) $registrationFeeData->sum('decimal_value');
    }

    /**
     * Calculates total registration fee over several years.
     *
     * @param Collection $registrationFeeData
     * @return Collection
     */
    private function totalRegistrationFeeOverYears(Collection $registrationFeeData): Collection
    {
        return $registrationFeeData->groupBy('year')->map(function ($items, $year) {
            $totalAmount = $items->sum('decimal_value');
            return [
                'year' => (int) $year,
                'total_amount' => (float) $totalAmount,
            ];
        })->values()->sortBy('year')->values();
    }

    /**
     * Calculates the total tuition fees paid.
     *
     * @param Collection $tuitionFeeData
     * @return float
     */
    private function totalTuitionFeesPaid(Collection $tuitionFeeData): float
    {
        return (float) $tuitionFeeData->sum('decimal_value');
    }

    /**
     * Formats total tuition fees paid over time.
     *
     * @param Collection $tuitionFeeData
     * @return array
     */
    private function totalTuitionFeesPaidOverTime(Collection $tuitionFeeData): array
    {
        return $this->formatDataOverTime($tuitionFeeData);
    }

    /**
     * Calculates the revenue from different sources for the current year.
     *
     * @param Collection $additionalFeeData
     * @param Collection $tuitionFeeData
     * @param Collection $registrationFeeData
     * @param Collection $resitFeeData
     * @return array
     */
    private function schoolRevenueSource(
        Collection $additionalFeeData,
        Collection $tuitionFeeData,
        Collection $registrationFeeData,
        Collection $resitFeeData
    ): array {
        // Filter data for the specific year (now()->year) as per original intent
        $currentYearRegistration = $registrationFeeData->where('year', now()->year);
        $currentYearResit = $resitFeeData->where('year', now()->year);

        return [
            'additional_fees' => (float) $additionalFeeData->sum('decimal_value'),
            'tuition_fees' => (float) $tuitionFeeData->sum('decimal_value'),
            'registration_fees' => (float) $currentYearRegistration->sum('decimal_value'),
            'resit_fees' => (float) $currentYearResit->sum('decimal_value'),
        ];
    }

    /**
     * Calculates total student registrations over several years.
     *
     * @param Collection $studentData
     * @return Collection
     */
    private function totalStudentRegistrationOverYears(Collection $studentData): Collection
    {
        return $studentData->groupBy('year')->map(function ($items, $year) {
            $totalStudents = $items->sum('integer_value');
            return [
                'year' => (int) $year,
                'total_student' => (int) $totalStudents,
            ];
        })->values()->sortBy('year')->values();
    }

    /**
     * Calculates the total student registrations for the current year.
     *
     * @param Collection $studentData
     * @return int
     */
    private function totalStudentRegistration(Collection $studentData): int
    {
        return (int) $studentData->where('year', now()->year)->sum('integer_value');
    }

    /**
     * Helper to update or create a progressive stat record.
     *
     * @param string $kpiId The ID of the StatType for the progressive stat.
     * @param float $value The decimal value to store.
     * @param object $currentSchool The current school branch object.
     * @param int $year The year of the stat.
     * @param int $month The month of the stat.
     * @return void
     */
    private function updateOrCreateProgressStat(
        string $kpiId,
        float $value,
        object $currentSchool,
        int $year,
        int $month
    ): void {
        $existingStat = DB::table('progressive_stats')
            ->where('stat_type_id', $kpiId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existingStat) {
            // Update existing record
            DB::table('progressive_stats')
                ->where('id', $existingStat->id)
                ->update([
                    'decimal_value' => $value,
                    'updated_at' => now(),
                ]);
        } else {
            // Insert new record
            DB::table('progressive_stats')->insert([
                'id' => Str::uuid(), // Generate a UUID for the ID
                'decimal_value' => $value,
                'stat_type_id' => $kpiId,
                'school_branch_id' => $currentSchool->id,
                'year' => $year,
                'month' => $month,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Helper to get the progressive stat value from the previous month.
     * Handles year transitions (e.g., Jan to Dec of previous year).
     *
     * @param string $kpiId The ID of the StatType.
     * @param object $currentSchool The current school branch object.
     * @param int $year The current year.
     * @param int $month The current month.
     * @return float The decimal value of the previous month's stat, or 0.0 if not found.
     */
    private function getPreviousMonthProgressStat(string $kpiId, object $currentSchool, int $year, int $month): float
    {
        $previousMonth = $month - 1;
        $previousYear = $year;

        // Adjust for January (month 1) to fetch December of the previous year
        if ($previousMonth === 0) {
            $previousMonth = 12;
            $previousYear--;
        }

        $oldValueRecord = DB::table('progressive_stats')
            ->where('stat_type_id', $kpiId)
            ->where('school_branch_id', $currentSchool->id)
            ->where('year', $previousYear)
            ->where('month', $previousMonth)
            ->first();

        return (float) ($oldValueRecord->decimal_value ?? 0.0);
    }

    /**
     * Helper to safely calculate percentage change.
     * Handles division by zero for the old value.
     *
     * @param float $newValue The new value.
     * @param float $oldValue The old value.
     * @return float The percentage change.
     */
    private function calculatePercentageChange(float $newValue, float $oldValue): float
    {
        if ($oldValue == 0.0) {
            // If old value is 0:
            // - If new value is also 0, no change (0%).
            // - If new value is positive, it's an infinite increase, represent as 100% or a very high number.
            // - If new value is negative, it's an infinite decrease, represent as -100% or a very low number.
            return $newValue == 0.0 ? 0.0 : ($newValue > 0.0 ? 100.0 : -100.0); // Simplified representation for practical use.
        }
        return (($newValue - $oldValue) / $oldValue) * 100.0;
    }

    /**
     * Calculates the total revenue progress for the current month.
     *
     * @param float $totalTuitionFeesPaidCurrentYear Total tuition fees paid for the current year.
     * @param float $totalRegistrationFeeCurrentYear Total registration fees for the current year.
     * @param float $totalAdditionalFeesCurrentYear Total additional fees for the current year.
     * @param float $totalResitFeesCurrentYear Total resit fees for the current year.
     * @param float $totalSchoolExpensesCurrentYear Total school expenses for the current year.
     * @param string|null $kpiId The StatType ID for 'total_revenue_progress'.
     * @param object $currentSchool The current school branch object.
     * @param int $year The current year.
     * @param int $month The current month.
     * @return array
     */
    private function calculateRevenueProgress(
        float $totalTuitionFeesPaidCurrentYear,
        float $totalRegistrationFeeCurrentYear,
        float $totalAdditionalFeesCurrentYear,
        float $totalResitFeesCurrentYear,
        float $totalSchoolExpensesCurrentYear,
        ?string $kpiId,
        object $currentSchool,
        int $year,
        int $month
    ): array {
        if (!$kpiId) {
            return [
                'total_revenue' => 0.0,
                'revenue_increase_stat' => ['value' => 0.0, 'percentage' => 0.0],
                'revenue_decrease_stat' => ['value' => 0.0, 'percentage' => 0.0],
            ];
        }

        // Calculate the new total revenue for the current month
        $newTotalRevenue = $totalTuitionFeesPaidCurrentYear +
                           $totalRegistrationFeeCurrentYear +
                           $totalAdditionalFeesCurrentYear +
                           $totalResitFeesCurrentYear -
                           $totalSchoolExpensesCurrentYear;

        // Get the previous month's recorded total revenue from progressive_stats
        $oldTotalRevenue = $this->getPreviousMonthProgressStat($kpiId, $currentSchool, $year, $month);

        // Update or insert the current month's total revenue into progressive_stats
        $this->updateOrCreateProgressStat($kpiId, $newTotalRevenue, $currentSchool, $year, $month);

        // Calculate increase/decrease values and percentages
        $revenueChangeValue = $newTotalRevenue - $oldTotalRevenue;
        $revenuePercentageChange = $this->calculatePercentageChange($newTotalRevenue, $oldTotalRevenue);

        $increaseStat = ['value' => 0.0, 'percentage' => 0.0];
        $decreaseStat = ['value' => 0.0, 'percentage' => 0.0];

        if ($revenueChangeValue > 0) {
            $increaseStat = ['value' => $revenueChangeValue, 'percentage' => $revenuePercentageChange];
        } elseif ($revenueChangeValue < 0) {
            // For decrease, value is absolute difference, percentage is negative
            $decreaseStat = ['value' => abs($revenueChangeValue), 'percentage' => $revenuePercentageChange];
        }

        return [
            'total_revenue' => (float) $newTotalRevenue,
            'revenue_increase_stat' => $increaseStat,
            'revenue_decrease_stat' => $decreaseStat
        ];
    }

    /**
     * Calculates the school expenses progress for the current month.
     *
     * @param float $totalSchoolExpensesCurrentYear Total school expenses for the current year.
     * @param string|null $kpiId The StatType ID for 'school_expenses_progress'.
     * @param object $currentSchool The current school branch object.
     * @param int $year The current year.
     * @param int $month The current month.
     * @return array
     */
    private function calculateSchoolExpenseProgress(
        float $totalSchoolExpensesCurrentYear,
        ?string $kpiId,
        object $currentSchool,
        int $year,
        int $month
    ): array {
        if (!$kpiId) {
            // Handle case where KPI ID is not found, return default empty stats.
            return [
                'expense_increase_stat' => ['value' => 0.0, 'percentage' => 0.0],
                'expense_decrease_stat' => ['value' => 0.0, 'percentage' => 0.0],
            ];
        }

        $newValueTotal = $totalSchoolExpensesCurrentYear;

        // Get the previous month's recorded expense from progressive_stats
        $oldValue = $this->getPreviousMonthProgressStat($kpiId, $currentSchool, $year, $month);

        // Update or insert the current month's total expenses into progressive_stats
        $this->updateOrCreateProgressStat($kpiId, $newValueTotal, $currentSchool, $year, $month);

        // Calculate increase/decrease values and percentages
        $expenseChangeValue = $newValueTotal - $oldValue;
        $percentageChange = $this->calculatePercentageChange($newValueTotal, $oldValue);

        $increaseStat = ['value' => 0.0, 'percentage' => 0.0];
        $decreaseStat = ['value' => 0.0, 'percentage' => 0.0];

        if ($expenseChangeValue > 0) {
            $increaseStat = ['value' => $expenseChangeValue, 'percentage' => $percentageChange];
        } elseif ($expenseChangeValue < 0) {
            // For decrease, value is absolute difference, percentage is negative
            $decreaseStat = ['value' => abs($expenseChangeValue), 'percentage' => $percentageChange];
        }

        return [
            'expense_increase_stat' => $increaseStat,
            'expense_decrease_stat' => $decreaseStat
        ];
    }
}
