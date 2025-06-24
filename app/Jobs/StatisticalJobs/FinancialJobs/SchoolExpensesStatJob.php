<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\SchoolExpenses;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SchoolExpensesStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the school expense record.
     * @var string
     */
    protected readonly string $schoolExpensesId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $schoolExpensesId The ID of the school expense.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $schoolExpensesId, string $schoolBranchId)
    {
        $this->schoolExpensesId = $schoolExpensesId;
        $this->schoolBranchId = $schoolBranchId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            'monthly_school_expenses'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $schoolExpenses = SchoolExpenses::where("school_branch_id", $this->schoolBranchId)
            ->find($this->schoolExpensesId);

        if (!$schoolExpenses) {
            Log::warning("School expense with ID '{$this->schoolExpensesId}' not found in school branch '{$this->schoolBranchId}'. Skipping school expenses stat job.");
            return;
        }

        $amount = (float) $schoolExpenses->amount;

        $monthlyExpensesKpi = $kpis->get('monthly_school_expenses');
        if ($monthlyExpensesKpi) {
            $this->upsertSchoolExpensesStat(
                $year,
                $month,
                $this->schoolBranchId,
                $monthlyExpensesKpi,
                $amount,
                $schoolExpenses->expenses_category_id
            );
        } else {
            Log::warning("StatType 'monthly_school_expenses' not found. Skipping monthly school expenses stat.");
        }
    }

    /**
     * Inserts or updates a school expenses statistic record, adding to its decimal_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param float $amount The amount to add to the decimal_value.
     * @return void
     */
    private function upsertSchoolExpensesStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        float $amount,
        string $categoryId
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'category_id' => $categoryId
        ];


        $existingStat = DB::table('school_expenses_stats')->where($matchCriteria)->first();

        if ($existingStat) {
            DB::table('school_expenses_stats')
                ->where($matchCriteria)
                ->increment('decimal_value', $amount, ['updated_at' => now()]);
            Log::info("Incremented existing school expenses stat for KPI '{$kpi->program_name}' by {$amount} for school: {$schoolBranchId}, year: {$year}, month: {$month}.");
        } else {

            DB::table('school_expenses_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'category_id' => $categoryId,
                'integer_value' => null,
                'decimal_value' => $amount,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new school expenses stat for KPI '{$kpi->program_name}' with initial amount {$amount} for school: {$schoolBranchId}, year: {$year}, month: {$month}.");
        }
    }
}
