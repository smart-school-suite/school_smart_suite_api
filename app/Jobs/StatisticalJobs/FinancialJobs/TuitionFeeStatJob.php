<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;


use App\Models\StatTypes;
use App\Models\TuitionFees;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TuitionFeeStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected readonly string $tuitionFeeId;
    protected readonly string $schoolBranchId;

    public function __construct(string $tuitionFeeId, string $schoolBranchId)
    {
        $this->tuitionFeeId = $tuitionFeeId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $year = now()->year;

        $kpiNames = [
            'total_tuition_fee_debt',
            'total_tuition_fee_debt_by_department',
            'total_tuition_fee_debt_by_specialty',
            'total_indepted_students',
            'total_indepted_student_by_department',
            'total_indepted_student_by_specialty',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        $tuitionFee = TuitionFees::where("school_branch_id", $this->schoolBranchId)
            ->with(['student.department', 'student.specialty'])
            ->find($this->tuitionFeeId);

        if (!$tuitionFee) {
            Log::warning("Tuition fee record with ID '{$this->tuitionFeeId}' not found in school branch '{$this->schoolBranchId}'. Skipping tuition fee stats job.");
            return;
        }

        $totalFeeAmount = (float) $tuitionFee->tution_fee_total;
        $specialtyId = $tuitionFee->student->specialty_id ?? null;
        $departmentId = $tuitionFee->student->department_id ?? null;

        // --- Update 'total_tuition_fee_debt' (Annual stat) ---
        $kpi = $kpis->get("total_tuition_fee_debt");
        if ($kpi) {
            $this->upsertFinancialStat(
                $year,
                null,
                $this->schoolBranchId,
                $kpi,
                $totalFeeAmount,
                null,
                null,
                'decimal_value'
            );
        } else {
            Log::warning("StatType 'total_tuition_fee_debt' not found. Skipping stat update.");
        }


        if ($departmentId) {
            // --- Update 'total_tuition_fee_debt_by_department' (Annual stat) ---
            $kpi = $kpis->get("total_tuition_fee_debt_by_department");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    $totalFeeAmount,
                    null,
                    $departmentId,
                    'decimal_value'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_debt_by_department' not found. Skipping stat update for dept: {$departmentId}.");
            }
        } else {
            Log::info("Student for tuition fee '{$this->tuitionFeeId}' has no department ID. Skipping department-based debt stat.");
        }


        if ($specialtyId) {
            // --- Update 'total_tuition_fee_debt_by_specialty' (Annual stat) ---
            $kpi = $kpis->get("total_tuition_fee_debt_by_specialty");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    $totalFeeAmount,
                    $specialtyId,
                    null,
                    'decimal_value'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_debt_by_specialty' not found. Skipping stat update for specialty: {$specialtyId}.");
            }
        } else {
            Log::info("Student for tuition fee '{$this->tuitionFeeId}' has no specialty ID. Skipping specialty-based debt stat.");
        }

        // --- Update 'total_indepted_students' (Annual stat) ---
        $kpi = $kpis->get("total_indepted_students");
        if ($kpi) {
            $this->upsertFinancialStat(
                $year,
                null,
                $this->schoolBranchId,
                $kpi,
                1, // Increment by 1
                null,
                null,
                'integer_value'
            );
        } else {
            Log::warning("StatType 'total_indepted_students' not found. Skipping stat update.");
        }

        if ($departmentId) {
            $kpi = $kpis->get("total_indepted_student_by_department");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    1,
                    null,
                    $departmentId,
                    'integer_value'
                );
            } else {
                Log::warning("StatType 'total_indepted_student_by_department' not found. Skipping stat update for dept: {$departmentId}.");
            }
        }

        if ($specialtyId) {
            $kpi = $kpis->get("total_indepted_student_by_specialty");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    1,
                    $specialtyId,
                    null,
                    'integer_value'
                );
            } else {
                Log::warning("StatType 'total_indepted_student_by_specialty' not found. Skipping stat update for specialty: {$specialtyId}.");
            }
        }
    }

    /**
     * Inserts or updates a financial statistic record in the school_financial_stats table.
     *
     * @param int $year The year for the statistic.
     * @param int|null $month The month for the statistic (null if not applicable, e.g., annual stats).
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI (non-nullable as checked in handle).
     * @param float|int $valueChange The value to add/subtract to the decimal_value or integer_value.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string $valueColumn 'decimal_value' or 'integer_value' indicating which column to update.
     * @return void
     */
    private function upsertFinancialStat(
        int $year,
        ?int $month,
        string $schoolBranchId,
        StatTypes $kpi,
        float|int $valueChange,
        ?string $specialtyId = null,
        ?string $departmentId = null,
        string $valueColumn = 'decimal_value'
    ): void {

        $matchCriteria = [
            'year' => $year,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'specialty_id' => $specialtyId,
            'department_id' => $departmentId,
            'month' => $month,
        ];


        $existingStat = DB::table('tuition_fee_stats')->where($matchCriteria)->first();

        if ($existingStat) {
            // If record exists, increment/decrement the specified value column
            if ($valueColumn === 'decimal_value') {
                DB::table('tuition_fee_stats')
                    ->where($matchCriteria)
                    ->increment('decimal_value', $valueChange, ['updated_at' => now()]);
            } elseif ($valueColumn === 'integer_value') {
                DB::table('tuition_fee_stats')
                    ->where($matchCriteria)
                    ->increment('integer_value', (int) $valueChange, ['updated_at' => now()]);
            }
            Log::info("Incremented existing financial stat for KPI '{$kpi->program_name}' ({$valueColumn}) by {$valueChange} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: " . ($month ?? 'N/A') . ".");
        } else {

            $insertData = [
                'id' => Str::uuid(),
                'year' => $year,
                'month' => $month,
                'school_branch_id' => $schoolBranchId,
                'stat_type_id' => $kpi->id,
                'specialty_id' => $specialtyId,
                'department_id' => $departmentId,
                'integer_value' => ($valueColumn === 'integer_value') ? (int) $valueChange : null,
                'decimal_value' => ($valueColumn === 'decimal_value') ? (float) $valueChange : null,
                'json_value' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('tuition_fee_stats')->insert($insertData);
            Log::info("Created new financial stat for KPI '{$kpi->program_name}' ({$valueColumn}) with initial value {$valueChange} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: " . ($month ?? 'N/A') . ".");
        }
    }
}
