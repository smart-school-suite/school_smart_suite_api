<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\SchoolExpenses;
use App\Models\Student;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SchoolExpensesStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $schoolExpensesId;
    public $schoolBranchId;
    public function __construct(string $schoolExpensesId, string $schoolBranchId)
    {
        //
        $this->schoolExpensesId = $schoolExpensesId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $schoolExpensesId = $this->schoolExpensesId;
        $schoolBranchId = $this->schoolBranchId;
        $year = now()->year;
        $month = now()->month;
        $kpiNames = [
            'monthly_school_expenses'
        ];
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');
        $schoolExpenses = SchoolExpenses::where("school_branch_id", $schoolBranchId)->find($schoolExpensesId);

        $this->monthlySchoolExpensesTotal(
            $year,
            $month,
            $schoolExpensesId,
            $kpis->get('monthly_school_expenses'),
            $schoolBranchId,
            $schoolExpenses
        );
    }

    private function monthlySchoolExpensesTotal($year, $month, $schoolExpensesId, $kpi, $schoolBranchId, $schoolExpenses)
    {
        $kpiData =  DB::table('school_financial_stats')->where("year", $year)
            ->where("month", $month)
            ->where("stat_type_id", $kpi->id)
            ->where("school_branch_id", $schoolBranchId)
            ->where("school_expenses_id", $schoolExpensesId)
            ->first();
        if ($kpiData) {
            $kpi->decimal_value += $schoolExpenses->amount;
            $kpi->save();
        }

        DB::table('school_financial_stats')->insert([
            'id' => Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'decimal_value' => $schoolExpenses->amount,
            'integer_value' => null,
            'json_value' => null,
            'stat_type_id' => $kpi->id ?? null,
            'month' => $month,
            'year' => $year,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
