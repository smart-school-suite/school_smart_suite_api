<?php

namespace App\Jobs\StatisticalJobs\FinancialJobs;

use App\Models\TuitionFeeTransactions;
use App\Models\StatTypes;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TuitionFeePaymentStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected readonly string $tuitionFeePaymentId;
    protected readonly string $schoolBranchId;

    public function __construct(string $tuitionFeePaymentId, string $schoolBranchId)
    {
        $this->tuitionFeePaymentId = $tuitionFeePaymentId;
        $this->schoolBranchId = $schoolBranchId;
    }

    public function handle(): void
    {
        $year = now()->year;
        $month = now()->month;

        $kpiNames = [
            'total_tuition_fee_debt',
            'total_tuition_fee_debt_by_department',
            'total_tuition_fee_debt_by_specialty',
            'total_tuition_fee_amount_paid',
            'total_tuition_fee_paid_by_department',
            'total_tuition_fee_paid_by_specialty',
            'total_indepted_students',
            'total_indepted_student_by_department',
            'total_indepted_student_by_specialty',
        ];

        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        $tuitionFeePayment = TuitionFeeTransactions::where("school_branch_id", $this->schoolBranchId)
            ->with(['tuition.student.department', 'tuition.student.specialty'])
            ->find($this->tuitionFeePaymentId);

        if (!$tuitionFeePayment) {
            Log::warning("Tuition fee transaction with ID '{$this->tuitionFeePaymentId}' not found in school branch '{$this->schoolBranchId}'. Skipping tuition fee stats job.");
            return;
        }

        if (!$tuitionFeePayment->tuition) {
            Log::warning("Tuition record missing for transaction ID '{$this->tuitionFeePaymentId}'. Cannot process tuition-related stats.");
            return;
        }

        $paidAmount = (float) $tuitionFeePayment->amount;
        $tuitionAmountLeft = (float) $tuitionFeePayment->tuition->amount_left;

        $studentDepartmentId = $tuitionFeePayment->tuition->student->department_id ?? null;
        $studentSpecialtyId = $tuitionFeePayment->tuition->student->specialty_id ?? null;

        // --- Update 'total_tuition_fee_debt' (Annual stat) ---
        $kpi = $kpis->get("total_tuition_fee_debt");
        if ($kpi) {
            $this->upsertFinancialStat(
                $year,
                null,
                $this->schoolBranchId,
                $kpi,
                -$paidAmount,
                null,
                null,
                'decimal_value',
                'tuition_fee_stats',
                'decrement'
            );
        } else {
            Log::warning("StatType 'total_tuition_fee_debt' not found. Skipping stat update.");
        }


        if ($studentDepartmentId) {
            // --- Update 'total_tuition_fee_debt_by_department' (Annual stat) ---
            $kpi = $kpis->get("total_tuition_fee_debt_by_department");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    -$paidAmount,
                    null,
                    $studentDepartmentId,
                    'decimal_value',
                    'tuition_fee_stats',
                    'decrement'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_debt_by_department' not found. Skipping stat update for dept: {$studentDepartmentId}.");
            }
        } else {
            Log::info("Student for tuition payment '{$this->tuitionFeePaymentId}' has no department ID. Skipping department-based debt stat.");
        }


        if ($studentSpecialtyId) {
            // --- Update 'total_tuition_fee_debt_by_specialty' (Annual stat) ---
            $kpi = $kpis->get("total_tuition_fee_debt_by_specialty");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    -$paidAmount,
                    $studentSpecialtyId,
                    null,
                    'decimal_value',
                    'tuition_fee_stats',
                    'decrement'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_debt_by_specialty' not found. Skipping stat update for specialty: {$studentSpecialtyId}.");
            }
        } else {
            Log::info("Student for tuition payment '{$this->tuitionFeePaymentId}' has no specialty ID. Skipping specialty-based debt stat.");
        }


        // --- Update 'total_tuition_fee_amount_paid' (Monthly stat) ---
        $kpi = $kpis->get("total_tuition_fee_amount_paid");
        if ($kpi) {
            $this->upsertFinancialStat(
                $year,
                $month,
                $this->schoolBranchId,
                $kpi,
                $paidAmount,
                null,
                null,
                'decimal_value',
                'tuition_fee_trans_stats',
                'increment'

            );
        } else {
            Log::warning("StatType 'total_tuition_fee_amount_paid' not found. Skipping stat update.");
        }


        if ($studentSpecialtyId) {
            // --- Update 'total_tuition_fee_paid_by_specialty' (Monthly stat) ---
            $kpi = $kpis->get("total_tuition_fee_paid_by_specialty");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $kpi,
                    $paidAmount,
                    $studentSpecialtyId,
                    null,
                    'decimal_value',
                    'tuition_fee_trans_stats',
                    'increment'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_paid_by_specialty' not found. Skipping stat update for specialty: {$studentSpecialtyId}.");
            }
        } else {
            Log::info("Student for tuition payment '{$this->tuitionFeePaymentId}' has no specialty ID. Skipping specialty-based paid stat.");
        }


        if ($studentDepartmentId) {
            $kpi = $kpis->get("total_tuition_fee_paid_by_department");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    $month,
                    $this->schoolBranchId,
                    $kpi,
                    $paidAmount,
                    null,
                    $studentDepartmentId,
                    'decimal_value',
                    'tuition_fee_trans_stats',
                    'increment'
                );
            } else {
                Log::warning("StatType 'total_tuition_fee_paid_by_department' not found. Skipping stat update for dept: {$studentDepartmentId}.");
            }
        } else {
            Log::info("Student for tuition payment '{$this->tuitionFeePaymentId}' has no department ID. Skipping department-based paid stat.");
        }

        if (abs($tuitionAmountLeft) < PHP_FLOAT_EPSILON) {

            $kpi = $kpis->get("total_indepted_students");
            if ($kpi) {
                $this->upsertFinancialStat(
                    $year,
                    null,
                    $this->schoolBranchId,
                    $kpi,
                    1,
                    null,
                    null,
                    'integer_value',
                    'tuition_fee_stats',
                    'decrement'
                );
            } else {
                Log::warning("StatType 'total_indepted_students' not found. Skipping stat update.");
            }

            if ($studentDepartmentId) {
                $kpi = $kpis->get("total_indepted_student_by_department");
                if ($kpi) {
                    $this->upsertFinancialStat(
                        $year,
                        null,
                        $this->schoolBranchId,
                        $kpi,
                        1,
                        null,
                        $studentDepartmentId,
                        'integer_value',
                        'tuition_fee_stats',
                        'decrement'
                    );
                } else {
                    Log::warning("StatType 'total_indepted_student_by_department' not found. Skipping stat update for dept: {$studentDepartmentId}.");
                }
            }

            if ($studentSpecialtyId) {
                $kpi = $kpis->get("total_indepted_student_by_specialty");
                if ($kpi) {
                    $this->upsertFinancialStat(
                        $year,
                        null,
                        $this->schoolBranchId,
                        $kpi,
                        1,
                        $studentSpecialtyId,
                        null,
                        'integer_value',
                        'tuition_fee_stats',
                        'decrement'
                    );
                } else {
                    Log::warning("StatType 'total_indepted_student_by_specialty' not found. Skipping stat update for specialty: {$studentSpecialtyId}.");
                }
            }
        }
    }

    /**
     * Inserts or updates a financial statistic record in the tuition_fee_trans_stats table.
     *
     * @param int $year The year for the statistic.
     * @param int|null $month The month for the statistic (null if not applicable, e.g., annual stats).
     * @param string $schoolBranchId The ID of the school branch.
     * @param StatTypes $kpi The StatTypes model instance for the KPI (non-nullable as checked in handle).
     * @param float|int $valueChange The value to add/subtract to the decimal_value or integer_value.
     * @param string|null $specialtyId The specialty ID, if the stat is specialty-specific.
     * @param string|null $departmentId The department ID, if the stat is department-specific.
     * @param string $valueColumn 'decimal_value' or 'integer_value' indicating which column to update.
     * @param string $dbTable The database table to update ('tuition_fee_stats' or 'tuition_fee_trans_stats').
     * @param string $actionType 'increment' or 'decrement' indicating whether to increment or decrement the value.
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
        string $valueColumn = 'decimal_value',
        string $dbTable,
        string $actionType
    ): void {

        $matchCriteria = [
            'year' => $year,
            'school_branch_id' => $schoolBranchId,
            'stat_type_id' => $kpi->id,
            'specialty_id' => $specialtyId,
            'department_id' => $departmentId,
        ];

        $matchCriteria['month'] = $month;

        $existingStat = DB::table($dbTable)->where($matchCriteria)->first();

        if ($existingStat) {
            $columnToUpdate = $valueColumn;
            $newValueChange = $valueChange;

            if ($actionType === 'increment') {
                DB::table($dbTable)
                    ->where($matchCriteria)
                    ->increment($columnToUpdate, $newValueChange, ['updated_at' => now()]);
                Log::info("Incremented existing financial stat for KPI '{$kpi->program_name}' ({$valueColumn}) by {$valueChange} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: " . ($month ?? 'N/A') . ".");
            } elseif ($actionType === 'decrement') {
                DB::table($dbTable)
                    ->where($matchCriteria)
                    ->decrement($columnToUpdate, abs($newValueChange), ['updated_at' => now()]); // Use abs for decrement to ensure positive value is passed
                Log::info("Decremented existing financial stat for KPI '{$kpi->program_name}' ({$valueColumn}) by {$valueChange} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: " . ($month ?? 'N/A') . ".");
            }

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

            // If the action is decrement for a new record, the initial value should be the negative of the valueChange
            if ($actionType === 'decrement') {
                if ($valueColumn === 'integer_value') {
                    $insertData['integer_value'] = (int) $valueChange;
                } elseif ($valueColumn === 'decimal_value') {
                    $insertData['decimal_value'] = (float) $valueChange;
                }
            }


            DB::table($dbTable)->insert($insertData);
            Log::info("Created new financial stat for KPI '{$kpi->program_name}' ({$valueColumn}) with initial value {$valueChange} for school: {$schoolBranchId}, dept: {$departmentId}, specialty: {$specialtyId}, year: {$year}, month: " . ($month ?? 'N/A') . ".");
        }
    }
}
