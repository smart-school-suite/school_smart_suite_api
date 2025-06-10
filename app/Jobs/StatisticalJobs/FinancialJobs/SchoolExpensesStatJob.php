<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\SchoolExpenses;
use App\Models\Student;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class SchoolExpensesStatJob implements ShouldQueue
{
     use Queueable;

    /**
     * Create a new job instance.
     */
    public $schoolExpenses;
    public function __construct(SchoolExpenses $schoolExpenses)
    {
        //
        $this->schoolExpenses = $schoolExpenses;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expenseDate = Carbon::parse($this->schoolExpenses->date);
        $year = $expenseDate->year;
        $month = $expenseDate->month;
        $totalNumberOfStudents = Student::where("school_branch_id", $this->schoolExpenses->school_branch_id)
            ->count();
        $generalStatByYearAndMonth = DB::table('school_financial_stats')
            ->where('school_branch_id', $this->schoolExpenses->school_branch_id)
            ->where('school_year', $this->schoolExpenses->school_year)
            ->where('year', $year)
            ->where('month', $month)
            ->join('stat_categories', 'school_financial_stats.stat_category_id', '=', 'stat_categories.id')
            ->where('stat_categories.program_name', 'school_expenses')
            ->join('stat_types', 'school_financial_stats.stat_type_id', '=', 'stat_types.id')
            ->select('school_financial_stats.*', 'stat_types.program_name as stat_type_program_name')
            ->get();


        $previousStatByYearAndMonth = DB::table('school_financial_stats')
            ->where('school_branch_id', $this->schoolExpenses->school_branch_id)
            ->where('school_year', $this->schoolExpenses->school_year)
            ->where('year', $month == 1 ? $year - 1 : $year)
            ->where('month', $month == 1 ? 12 : $month - 1)
            ->join('stat_categories', 'school_financial_stats.stat_category_id', '=', 'stat_categories.id')
             ->where('stat_categories.program_name', 'school_expenses')
            ->join('stat_types', 'school_financial_id', '=', 'stat_types.id')
            ->select('school_financial_stats.*', 'stat_types.program_name as stat_type_program_name')
            ->get();
    }

    public function calculateTotalExpensesByMonth($generalStatByYearAndMonth){
        if ($generalStatByYearAndMonth->isEmpty()) {
            return [
                'total_expenses' => $this->schoolExpenses->amount,
            ];
        }

        $totalExpensesStat = $generalStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();

        $total = $this->schoolExpenses->amount + ($totalExpensesStat ? $totalExpensesStat->stat_value : 0);

        return [
            'total_expenses' => $total,
        ];
    }

    public function calculatePercentageIncreaseByMonth($previousStatByYearAndMonth, $generalStatByYearAndMonth): float
    {
        $currentTotalExpenses = 0;
        $previousTotalExpenses = 0;

        if ($generalStatByYearAndMonth->isNotEmpty()) {
            $currentTotalExpensesStat = $generalStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();
            $currentTotalExpenses = $currentTotalExpensesStat ? $currentTotalExpensesStat->stat_value : 0;
        }

        if ($previousStatByYearAndMonth->isNotEmpty()) {
            $previousTotalExpensesStat = $previousStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();
            $previousTotalExpenses = $previousTotalExpensesStat ? $previousTotalExpensesStat->stat_value : 0;
        }
        if ($previousTotalExpenses == 0) {
            return 0;
        }
        return (($currentTotalExpenses - $previousTotalExpenses) / $previousTotalExpenses) * 100;
    }

    public function calculatePercentageDecreaseByMonth($previousStatByYearAndMonth, $generalStatByYearAndMonth): float
    {
        $currentTotalExpenses = 0;
        $previousTotalExpenses = 0;

         if ($generalStatByYearAndMonth->isNotEmpty()) {
            $currentTotalExpensesStat = $generalStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();
            $currentTotalExpenses = $currentTotalExpensesStat ? $currentTotalExpensesStat->stat_value : 0;
        }

        if ($previousStatByYearAndMonth->isNotEmpty()) {
            $previousTotalExpensesStat = $previousStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();
            $previousTotalExpenses = $previousTotalExpensesStat ? $previousTotalExpensesStat->stat_value : 0;
        }
        if ($previousTotalExpenses == 0) {
            return 0;
        }
        return (($previousTotalExpenses - $currentTotalExpenses) / $previousTotalExpenses) * 100;
    }
    public function getGroupedExpensesByMonth($month, $year,  $schoolBranchId){
        $groupedExpenses = SchoolExpenses::select([
            'school_expenses_category.name as category_name',
            DB::raw('SUM(school_expenses.amount) as total_amount'),
        ])
        ->join(
            'school_expenses_category',
            'school_expenses.expenses_category_id',
            '=',
            'school_expenses_category.id'
        )
        ->where('school_expenses.school_branch_id', $schoolBranchId)
        ->whereYear('school_expenses.date', $year)
        ->whereMonth('school_expenses.date', $month)
        ->groupBy('school_expenses_category.name')
        ->get();

    return $groupedExpenses->toArray();
    }

    public function costPerStudent($generalStatByYearAndMonth, $totalNumberOfStudents){
        if ($generalStatByYearAndMonth->isEmpty()) {
            return [
                'total_expenses' => $this->schoolExpenses->amount,
            ];
        }

        $totalExpensesStat = $generalStatByYearAndMonth->where('stat_type_program_name', 'total_expenses')->first();

        $total = $this->schoolExpenses->amount + ($totalExpensesStat ? $totalExpensesStat->stat_value : 0);
        $costPerStudent = $total / $totalNumberOfStudents;
        return [
            'cost_per_student' => $costPerStudent,
            'total_expenses_stat' => $totalExpensesStat
        ];

    }
}
