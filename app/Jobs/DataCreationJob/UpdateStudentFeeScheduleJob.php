<?php

namespace App\Jobs\DataCreationJob;

use App\Models\FeeScheduleSlot;
use App\Models\Specialty;
use App\Models\StudentFeeSchedule;
use App\Models\TuitionFees;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateStudentFeeScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    protected string $feeScheduleId;
    protected string $schoolBranchId;
    protected string $specialtyId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $feeScheduleId, string $schoolBranchId, string $specialtyId)
    {
        $this->feeScheduleId = $feeScheduleId;
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Use transactions for data integrity
        DB::beginTransaction();

        try {
            $feeScheduleSlots = FeeScheduleSlot::where("school_branch_id", $this->schoolBranchId)
                ->where("fee_schedule_id", $this->feeScheduleId)
                ->get();

            $specialty = Specialty::where("school_branch_id", $this->schoolBranchId)
                ->findOrFail($this->specialtyId);

            $studentDebts = $this->getStudentDebts($specialty, $this->schoolBranchId);

            $this->createStudentFeePaymentSchedule($feeScheduleSlots, $studentDebts, $this->schoolBranchId);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

        /**
     * Get student debts based on specialty and school branch.
     *
     * @param Specialty $specialty
     * @param string $schoolBranchId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getStudentDebts(Specialty $specialty, string $schoolBranchId): \Illuminate\Database\Eloquent\Collection
    {
        return TuitionFees::where("school_branch_id", $schoolBranchId)
            ->where("specialty_id", $specialty->id)
            ->where("level_id", $specialty->level_id)
            ->get();
    }

    /**
     * Create student fee payment schedules.
     *
     * @param \Illuminate\Database\Eloquent\Collection $feeScheduleSlots
     * @param \Illuminate\Database\Eloquent\Collection $studentDebts
     * @param string $schoolBranchId
     * @return void
     */
    protected function createStudentFeePaymentSchedule(
        \Illuminate\Database\Eloquent\Collection $feeScheduleSlots,
        \Illuminate\Database\Eloquent\Collection $studentDebts,
        string $schoolBranchId
    ): void {
        foreach ($studentDebts as $debt) {
            foreach ($feeScheduleSlots as $slot) {
                $totalDebt = $debt->tution_fee_total;
                $percentage = $slot->fee_percentage / 100;
                $installment = $totalDebt * $percentage;

                StudentFeeSchedule::create([
                    'student_id' => $debt->student_id,
                    'level_id' => $debt->level_id,
                    'specialty_id' => $debt->specialty_id,
                    'school_branch_id' => $schoolBranchId,
                    'expected_amount' => $installment,
                    'fee_schedule_slot_id' => $slot->id,
                    'fee_schedule_id' => $slot->fee_schedule_id,
                ]);
            }
        }
    }

}
