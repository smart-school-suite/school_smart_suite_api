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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CreateStudentFeeScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $maxExceptions = 1;
    public $timeout = 1200; // 20 minutes max
    public $backoff = [10, 30, 60];

    protected string $feeScheduleId;
    protected string $schoolBranchId;
    protected string $specialtyId;

    public function __construct(string $feeScheduleId, string $schoolBranchId, string $specialtyId)
    {
        $this->feeScheduleId = $feeScheduleId;
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyId = $specialtyId;
    }

    public function handle(): void
    {

        DB::beginTransaction();

        try {
            $feeScheduleSlots = FeeScheduleSlot::where('school_branch_id', $this->schoolBranchId)
                ->where('fee_schedule_id', $this->feeScheduleId)
                ->get();

            if ($feeScheduleSlots->isEmpty()) {
                DB::rollBack();
                return;
            }

            $specialty = Specialty::where('school_branch_id', $this->schoolBranchId)
                ->findOrFail($this->specialtyId);

            $studentDebts = TuitionFees::where('school_branch_id', $this->schoolBranchId)
                ->where('specialty_id', $specialty->id)
                ->where('level_id', $specialty->level_id)
                ->select('id', 'student_id', 'level_id', 'specialty_id', 'tution_fee_total')
                ->get();

            if ($studentDebts->isEmpty()) {
                Log::info('No students with tuition fees found for this specialty', [
                    'specialty_id' => $specialty->id,
                    'level_id' => $specialty->level_id
                ]);
                DB::commit();
                return;
            }

            $this->createStudentFeePaymentSchedule($feeScheduleSlots, $studentDebts, $this->schoolBranchId);

            DB::commit();


        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    protected function createStudentFeePaymentSchedule(
        $feeScheduleSlots,
        $studentDebts,
        string $schoolBranchId
    ): void {
        $now = now();

        $studentDebts->chunk(100)->each(function ($debts) use ($feeScheduleSlots, $schoolBranchId, $now) {
            $records = [];

            foreach ($debts as $debt) {
                foreach ($feeScheduleSlots as $slot) {
                    $installmentAmount = $debt->tution_fee_total * ($slot->fee_percentage / 100);

                    $records[] = [
                        'id' => Str::uuid()->toString(),
                        'student_id'           => $debt->student_id,
                        'level_id'             => $debt->level_id,
                        'specialty_id'         => $debt->specialty_id,
                        'school_branch_id'     => $schoolBranchId,
                        'expected_amount'      => $installmentAmount,
                        'fee_schedule_slot_id' => $slot->id,
                        'fee_schedule_id'      => $slot->fee_schedule_id,
                        'tuition_fee_id'       => $debt->id,
                        'amount_paid'          => 0,
                        'amount_left'          => $installmentAmount,
                        'percentage_paid'      => 0,
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];
                }
            }

            if (!empty($records)) {
                StudentFeeSchedule::insert($records);
            }
        });
    }
}
