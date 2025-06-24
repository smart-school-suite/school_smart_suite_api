<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\AdditionalFees; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class AdditionalFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the additional fee being processed.
     * @var string
     */
    protected readonly string $additionalFeeId;

    /**
     * The ID of the school branch.
     * @var string
     */
    protected readonly string $schoolBranchId;

    /**
     * Create a new job instance.
     *
     * @param string $additionalFeeId The ID of the additional fee.
     * @param string $schoolBranchId The ID of the school branch.
     */
    public function __construct(string $additionalFeeId, string $schoolBranchId)
    {
        $this->additionalFeeId = $additionalFeeId;
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
            "total_additional_fee",
            "total_additional_fee_by_department",
            "total_additional_fee_by_specialty"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $additionalFeeDetails = AdditionalFees::where("school_branch_id", $this->schoolBranchId)
            ->with(['student.department', 'student.specialty'])
            ->find($this->additionalFeeId);

        if (!$additionalFeeDetails) {
            Log::warning("Additional fee with ID '{$this->additionalFeeId}' not found in school branch '{$this->schoolBranchId}'. Skipping additional fee stats job.");
            return;
        }

        $amount = (float) $additionalFeeDetails->amount;

        $totalAdditionalFeeKpi = $kpis->get('total_additional_fee');
        if ($totalAdditionalFeeKpi) {
            $this->upsertAdditionalFeeStat(
                $year,
                $month,
                $this->schoolBranchId,
                $totalAdditionalFeeKpi,
                $amount,
                null,
                null,
                $additionalFeeDetails->additionalfee_category_id
            );
        } else {
            Log::warning("StatType 'total_additional_fee' not found. Skipping general additional fee stat.");
        }


        if ($additionalFeeDetails->student) {
            $byDepartmentKpi = $kpis->get('total_additional_fee_by_department');
            if ($byDepartmentKpi) {
                if ($additionalFeeDetails->student->department_id) {
                    $this->upsertAdditionalFeeStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $byDepartmentKpi,
                        $amount,
                        $additionalFeeDetails->student->department_id,
                        null,
                        $additionalFeeDetails->additionalfee_category_id
                    );
                } else {
                    Log::info("Student for additional fee '{$this->additionalFeeId}' has no department ID. Skipping department-based additional fee stat.");
                }
            } else {
                Log::warning("StatType 'total_additional_fee_by_department' not found. Skipping department-based additional fee stat.");
            }


            $bySpecialtyKpi = $kpis->get('total_additional_fee_by_specialty');
            if ($bySpecialtyKpi) {
                if ($additionalFeeDetails->student->specialty_id) {
                    $this->upsertAdditionalFeeStat(
                        $year,
                        $month,
                        $this->schoolBranchId,
                        $bySpecialtyKpi,
                        $amount,
                        null,
                        $additionalFeeDetails->student->specialty_id,
                        $additionalFeeDetails->additionalfee_category_id
                    );
                } else {
                    Log::info("Student for additional fee '{$this->additionalFeeId}' has no specialty ID. Skipping specialty-based additional fee stat.");
                }
            } else {
                Log::warning("StatType 'total_additional_fee_by_specialty' not found. Skipping specialty-based additional fee stat.");
            }
        } else {
            Log::warning("Student relationship for additional fee '{$this->additionalFeeId}' is missing. Cannot calculate department/specialty based stats.");
        }
    }

    /**
     * Inserts or updates an additional fee statistic record, summing its decimal_value.
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
    private function upsertAdditionalFeeStat(
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

        $existingStat = DB::table('additional_fee_stats')->where($matchCriteria)->first();

        if ($existingStat) {
            DB::table('additional_fee_stats')
                ->where($matchCriteria)
                ->increment('decimal_value', $amount, ['updated_at' => now()]);
            Log::info("Incremented existing additional fee stat for KPI '{$kpi->program_name}' by {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        } else {
            DB::table('additional_fee_stats')->insert([
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
            Log::info("Created new additional fee stat for KPI '{$kpi->program_name}' with initial amount {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: {$month}.");
        }
    }
}
