<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\ResitFeeTransactions; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class ResitFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the resit fee transaction.
     * @var string
     */
    protected readonly string $resitTransactionId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $resitTransactionId The ID of the resit fee transaction.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $resitTransactionId, string $schoolBranchId)
    {
        $this->resitTransactionId = $resitTransactionId;
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
            'resit_fee_total_amount_paid',
            'resit_fee_total_amount_paid_by_specialty',
            'resit_fee_total_amount_paid_by_department'
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $resitDetails = ResitFeeTransactions::where("school_branch_id", $this->schoolBranchId)
            ->with(['studentResit.student.department', 'studentResit.student.specialty'])
            ->find($this->resitTransactionId);

        if (!$resitDetails) {
            Log::warning("Resit fee transaction with ID '{$this->resitTransactionId}' not found in school branch '{$this->schoolBranchId}'. Skipping resit fee stats job.");
            return;
        }

        $amount = (float) $resitDetails->amount;

        // --- Update 'resit_fee_total_amount_paid' ---
        $totalAmountPaidKpi = $kpis->get('resit_fee_total_amount_paid');
        if ($totalAmountPaidKpi) {
            $this->upsertResitFeeStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalAmountPaidKpi,
                $amount
            );
        } else {
            Log::warning("StatType 'resit_fee_total_amount_paid' not found. Skipping general resit fee stat.");
        }


        if ($resitDetails->studentResit && $resitDetails->studentResit->student) {
            $student = $resitDetails->studentResit->student;

            $byDepartmentKpi = $kpis->get('resit_fee_total_amount_paid_by_department');
            if ($byDepartmentKpi) {
                if ($student->department_id) {
                    $this->upsertResitFeeStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byDepartmentKpi,
                        $amount,
                        $student->department_id,
                        null
                    );
                } else {
                    Log::info("Student for resit transaction '{$this->resitTransactionId}' has no department ID. Skipping department-based resit fee stat.");
                }
            } else {
                Log::warning("StatType 'resit_fee_total_amount_paid_by_department' not found. Skipping department-based resit fee stat.");
            }

            $bySpecialtyKpi = $kpis->get('resit_fee_total_amount_paid_by_specialty');
            if ($bySpecialtyKpi) {
                if ($student->specialty_id) {
                    $this->upsertResitFeeStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $bySpecialtyKpi,
                        $amount,
                        null,
                        $student->specialty_id
                    );
                } else {
                    Log::info("Student for resit transaction '{$this->resitTransactionId}' has no specialty ID. Skipping specialty-based resit fee stat.");
                }
            } else {
                Log::warning("StatType 'resit_fee_total_amount_paid_by_specialty' not found. Skipping specialty-based resit fee stat.");
            }
        } else {
            Log::warning("Nested relationships (studentResit or student) missing for resit transaction '{$this->resitTransactionId}'. Cannot calculate department/specialty based stats.");
        }
    }

    /**
     * Inserts or updates a resit fee statistic record, adding to its decimal_value.
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
    private function upsertResitFeeStat(
        int $year,
        int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        float $amount,
        ?string $departmentId = null,
        ?string $specialtyId = null
    ): void {
        $matchCriteria = [
            'year' => $year,
            'month' => $month,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'department_id' => $departmentId,
            'specialty_id' => $specialtyId,
        ];


        $existingStat = DB::table('resit_fee_trans_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('resit_fee_trans_stats')
                ->where($matchCriteria)
                ->increment('decimal_value', $amount, ['updated_at' => now()]);
            Log::info("Incremented existing resit fee stat for KPI '{$kpi->program_name}' by {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {

            DB::table('resit_fee_trans_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'department_id' => $departmentId,
                'specialty_id' => $specialtyId,
                'integer_value' => null,
                'decimal_value' => $amount,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Created new resit fee stat for KPI '{$kpi->program_name}' with initial amount {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }
}
