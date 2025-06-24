<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\AdditionalFeeTransactions;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdditionalFeeTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the additional fee transaction.
     * @var string
     */
    protected readonly string $transactionId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $transactionId The ID of the additional fee transaction.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $transactionId, string $schoolBranchId)
    {
        $this->transactionId = $transactionId;
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
            "additional_fee_total_amount_paid",
            "additional_fee_total_amount_paid_by_department",
            "additional_fee_total_amount_paid_by_specialty"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $additionalFeeTransaction = AdditionalFeeTransactions::where("school_branch_id", $this->schoolBranchId)
            ->with(['additionFee.student.department', 'additionFee.student.specialty'])
            ->find($this->transactionId);

        if (!$additionalFeeTransaction) {
            Log::warning("Additional fee transaction with ID '{$this->transactionId}' not found in school branch '{$this->schoolBranchId}'. Skipping additional fee transaction stats job.");
            return;
        }

        $amount = (float) $additionalFeeTransaction->amount;


        $totalAmountPaidKpi = $kpis->get('additional_fee_total_amount_paid');
        if ($totalAmountPaidKpi) {
            $this->upsertAdditionalFeeTransactionStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalAmountPaidKpi,
                $amount,
                null,
                null,
                $additionalFeeTransaction->additionFee->additionalfee_category_id
            );
        } else {
            Log::warning("StatType 'additional_fee_total_amount_paid' not found. Skipping general additional fee transaction amount stat.");
        }


        if ($additionalFeeTransaction->additionFee && $additionalFeeTransaction->additionFee->student) {
            $student = $additionalFeeTransaction->additionFee->student;

            $byDepartmentKpi = $kpis->get('additional_fee_total_amount_paid_by_department');
            if ($byDepartmentKpi) {
                if ($student->department_id) {
                    $this->upsertAdditionalFeeTransactionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byDepartmentKpi,
                        $amount,
                        $student->department_id,
                        null,
                        $additionalFeeTransaction->additionFee->additionalfee_category_id
                    );
                } else {
                    Log::info("Student for additional fee transaction '{$this->transactionId}' has no department ID. Skipping department-based amount paid stat.");
                }
            } else {
                Log::warning("StatType 'additional_fee_total_amount_paid_by_department' not found. Skipping department-based additional fee transaction amount stat.");
            }

            $bySpecialtyKpi = $kpis->get('additional_fee_total_amount_paid_by_specialty');
            if ($bySpecialtyKpi) {
                if ($student->specialty_id) {
                    $this->upsertAdditionalFeeTransactionStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $bySpecialtyKpi,
                        $amount,
                        null,
                        $student->specialty_id,
                        $additionalFeeTransaction->additionFee->additionalfee_category_id
                    );
                } else {
                    Log::info("Student for additional fee transaction '{$this->transactionId}' has no specialty ID. Skipping specialty-based amount paid stat.");
                }
            } else {
                Log::warning("StatType 'additional_fee_total_amount_paid_by_specialty' not found. Skipping specialty-based additional fee transaction amount stat.");
            }
        } else {
            Log::warning("Nested relationships (additionFee or student) missing for additional fee transaction '{$this->transactionId}'. Cannot calculate department/specialty based stats.");
        }
    }

    /**
     * Inserts or updates an additional fee transaction statistic record, adding to its decimal_value.
     *
     * @param int $year The year for the statistic.
     * @param int $month The month for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param float $amount The amount to add to the decimal_value.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @return void
     */
    private function upsertAdditionalFeeTransactionStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        float $amount,
        ?string $departmentId = null,
        ?string $specialtyId = null,
        string $categoryId
    ): void {

        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'department_id' => $departmentId,
            'specialty_id' => $specialtyId,
            'category_id' => $categoryId
        ];


        $existingStat = DB::table('additional_fee_trans_stats')->where($matchCriteria)->first();

        if ($existingStat) {
            DB::table('additional_fee_trans_stats')
                ->where($matchCriteria)
                ->increment('decimal_value', $amount, ['updated_at' => now()]);
            Log::info("Incremented existing additional fee transaction stat for KPI '{$kpi->program_name}' by {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {
            DB::table('additional_fee_trans_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'department_id' => $departmentId,
                'specialty_id' => $specialtyId,
                'category_id' => $categoryId,
                'integer_value' => null,
                'decimal_value' => $amount,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new additional fee transaction stat for KPI '{$kpi->program_name}' with initial amount {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }
}
