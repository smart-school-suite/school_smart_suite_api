<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\RegistrationFeeTransactions; // Ensure this model is correctly configured
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Support\Str;

class RegistrationFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The ID of the registration fee transaction.
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
     * @param string $transactionId The ID of the registration fee transaction.
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

        $kpiNames = [
            "registration_fee_total_amount_paid",
            "registration_fee_total_amount_paid_by_department",
            "registration_fee_total_amount_paid_by_specialty"
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $registrationFeeDetails = RegistrationFeeTransactions::where("school_branch_id", $this->schoolBranchId)
            ->with(['registrationFee.student.department', 'registrationFee.student.specialty'])
            ->find($this->transactionId);

        if (!$registrationFeeDetails) {
            Log::warning("Registration fee transaction with ID '{$this->transactionId}' not found in school branch '{$this->schoolBranchId}'. Skipping registration fee stats job.");
            return;
        }

        $amount = (float) $registrationFeeDetails->amount;


        $totalAmountPaidKpi = $kpis->get('registration_fee_total_amount_paid');
        if ($totalAmountPaidKpi) {
            $this->upsertRegistrationFeeStat(
                $year,
                $this->schoolBranchId,
                $totalAmountPaidKpi,
                $amount,
                null,
                null
            );
        } else {
            Log::warning("StatType 'registration_fee_total_amount_paid' not found. Skipping general registration fee transaction amount stat.");
        }


        if ($registrationFeeDetails->registrationFee && $registrationFeeDetails->registrationFee->student) {
            $student = $registrationFeeDetails->registrationFee->student;

            $bySpecialtyKpi = $kpis->get('registration_fee_total_amount_paid_by_specialty');
            if ($bySpecialtyKpi) {
                if ($student->specialty_id) {
                    $this->upsertRegistrationFeeStat(
                        $year,
                        $this->schoolBranchId,
                        $bySpecialtyKpi,
                        $amount,
                        null,
                        $student->specialty_id
                    );
                } else {
                    Log::info("Student for transaction '{$this->transactionId}' has no specialty ID. Skipping specialty-based amount paid stat.");
                }
            } else {
                Log::warning("StatType 'registration_fee_total_amount_paid_by_specialty' not found. Skipping specialty-based registration fee transaction amount stat.");
            }

            $byDepartmentKpi = $kpis->get('registration_fee_total_amount_paid_by_department');
            if ($byDepartmentKpi) {
                if ($student->department_id) {
                    $this->upsertRegistrationFeeStat(
                        $year,
                        $this->schoolBranchId,
                        $byDepartmentKpi,
                        $amount,
                        $student->department_id,
                        null
                    );
                } else {
                    Log::info("Student for transaction '{$this->transactionId}' has no department ID. Skipping department-based amount paid stat.");
                }
            } else {
                Log::warning("StatType 'registration_fee_total_amount_paid_by_department' not found. Skipping department-based registration fee transaction amount stat.");
            }
        } else {
            Log::warning("Student details or registration fee relationship missing for transaction '{$this->transactionId}'. Cannot calculate department/specialty based stats.");
        }
    }

    /**
     * Inserts or updates a registration fee statistic record, adding to its decimal_value.
     *
     * @param int $year The year for the statistic.
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI. (Non-nullable here as checked in handle)
     * @param float $amount The amount to add to the decimal_value.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @return void
     */
    private function upsertRegistrationFeeStat(
        int $year,
        string $schoolBranchId,
        StatTypes $kpi,
        float $amount,
        ?string $departmentId = null,
        ?string $specialtyId = null
    ): void {

        $matchCriteria = [
            'year' => $year,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'department_id' => $departmentId,
            'specialty_id' => $specialtyId,
        ];


        $existingStat = DB::table('registration_fee_stats')->where($matchCriteria)->first();

        if ($existingStat) {

            DB::table('registration_fee_stats')
                ->where($matchCriteria)
                ->increment('decimal_value', $amount, ['updated_at' => now()]);
            Log::info("Incremented existing registration fee stat for KPI '{$kpi->program_name}' by {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}.");
        } else {

            DB::table('registration_fee_stats')->insert([
                'id' => Str::uuid(),
                'year' => $year,
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
            Log::info("Created new registration fee stat for KPI '{$kpi->program_name}' with initial amount {$amount} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}.");
        }
    }
}
