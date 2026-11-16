<?php

namespace App\Services\TuitionFee;

use App\Jobs\NotificationJobs\SendAdminFeeScheduleNotificationJob;
use App\Jobs\NotificationJobs\SendFeeScheduleNotificationJob;
use App\Models\FeeSchedule;
use App\Models\FeeScheduleSlot;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;

class TuitionFeeScheduleSlotService
{
    public function createFeeScheduleSlots(Schoolbranches $currentSchool, string $feeScheduleId, array $slotsData, $authAdmin): bool
    {
        DB::beginTransaction();
        try {

            $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);
            $specialty = $feeSchedule->schoolYear->specialty;
            $scheduleData = [
                'schoolYear' => $feeSchedule->schoolYear->systemAcademicYear?->name,
                'specialty' => $specialty->specialty_name,
                'level' => $specialty->level?->level,
                'semester' => null,
            ];

            $slotsToCreate = [];

            foreach ($slotsData as $index => $slotData) {
                if (!isset($slotData['due_date'], $slotData['fee_percentage'], $slotData['installment_id'])) {
                    throw new \InvalidArgumentException("Missing required data for slot #{$index}. Required: due_date, fee_percentage, installment_id.");
                }

                if (!is_numeric($slotData['fee_percentage'])) {
                    throw new \InvalidArgumentException("Invalid fee_percentage for slot #{$index}. Must be numeric.");
                }

                $slotsToCreate[] = [
                    'id'               => (string) Str::uuid(),
                    'due_date'         => $slotData['due_date'],
                    'percentage'       => (float) $slotData['fee_percentage'],
                    'installment_id'   => $slotData['installment_id'],
                    'fee_schedule_id'  => $feeSchedule->id,
                    'school_branch_id' => $currentSchool->id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            FeeScheduleSlot::insert($slotsToCreate);
            DB::commit();
            SendAdminFeeScheduleNotificationJob::dispatch(
                $currentSchool->id,
                $scheduleData
            );
            SendFeeScheduleNotificationJob::dispatch(
                $currentSchool->id,
                $specialty->id,
                $scheduleData
            );
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.feeScheduleSlots.create"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeSheduleSlotMangement",
                    "action" => "tuitionFeeScheduleSlot.created",
                    "authAdmin" => $authAdmin,
                    "data" => $feeSchedule,
                    "message" => "Tuition Fee Schedule Slots Created",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => [$specialty->id],
                'feature'      => 'tuitionFeeScheduleCreate',
                'message'      => 'Tuition Fee Schedule Created',
                'data'         => $feeSchedule,
            ]);
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("Fee Schedule (ID: {$feeScheduleId}) not found for school branch (ID: {$currentSchool->id}).", ['exception' => $e]);
            throw new ModelNotFoundException("The specified Fee Schedule was not found for this school branch.");
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            Log::error("Invalid argument provided for creating fee schedule slots: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create fee schedule slots for Fee Schedule ID: {$feeScheduleId}. Error: " . $e->getMessage(), ['exception' => $e]);
            throw new \Exception("An unexpected error occurred while creating fee schedule slots.", 0, $e);
        }
    }
    public function updateFeeScheduleSlots(Schoolbranches $currentSchool, string $feeScheduleId, array $slotsData, $authAdmin): bool
    {
        DB::beginTransaction();

        try {

            $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);

            foreach ($slotsData as $index => $slotData) {
                if (!isset($slotData['slot_id'])) {
                    throw new \InvalidArgumentException("Missing 'id' for slot at index {$index}. Each slot to update must have an ID.");
                }

                $slot = FeeScheduleSlot::where("school_branch_id", $currentSchool->id)
                    ->where("fee_schedule_id", $feeScheduleId)
                    ->findOrFail($slotData['slot_id']);

                $updatePayload = [];

                if (isset($slotData['due_date']) && !is_null($slotData['due_date'])) {
                    $updatePayload['due_date'] = $slotData['due_date'];
                }

                if (isset($slotData['fee_percentage']) && !is_null($slotData['fee_percentage'])) {
                    if (!is_numeric($slotData['fee_percentage'])) {
                        throw new \InvalidArgumentException("Invalid fee_percentage for slot ID {$slot->id}. Must be numeric.");
                    }
                    $percentage = (float) $slotData['fee_percentage'];

                    if ($percentage < 0 || $percentage > 100) {
                        throw new \InvalidArgumentException("Fee percentage for slot ID {$slot->id} must be between 0 and 100.");
                    }

                    $updatePayload['percentage'] = $percentage;
                }

                if (isset($slotData['installment_id']) && !is_null($slotData['installment_id'])) {
                    $updatePayload['installment_id'] = $slotData['installment_id'];
                }

                if (!empty($updatePayload)) {
                    $slot->update($updatePayload);
                }
            }

            DB::commit();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.feeScheduleSlot.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeSheduleSlotMangement",
                    "action" => "tuitionFeeScheduleSlot.updated",
                    "authAdmin" => $authAdmin,
                    "data" => $feeSchedule,
                    "message" => "Tuition Fee Schedule Slots Updated",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'specialtyIds'   => [$feeSchedule->schoolYear->specialty_id],
                'feature'      => 'tuitionFeeScheduleUpdate',
                'message'      => 'Tuition Fee Schedule Updated',
                'data'         => $feeSchedule,
            ]);
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error("Fee Schedule (ID: {$feeScheduleId}) not found for school branch (ID: {$currentSchool->id}) or a specified slot was not found.", ['exception' => $e]);
            throw new ModelNotFoundException("The specified Fee Schedule or one of its slots was not found for this school branch.");
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            Log::error("Invalid argument for updating fee schedule slots (Fee Schedule ID: {$feeScheduleId}): " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to update fee schedule slots for Fee Schedule ID: {$feeScheduleId}. Error: " . $e->getMessage(), ['exception' => $e]);
            throw new \Exception("An unexpected error occurred while updating fee schedule slots.", 0, $e);
        }
    }
    public function deleteFeeScheduleSlot(string $feeScheduleId, $currentSchool, $authAdmin)
    {
        $feeSchedules = FeeSchedule::where("school_branch_id", $currentSchool->id)
            ->where("fee_schedule_id", $feeScheduleId)
            ->get();
        foreach ($feeSchedules as $feeSchedule) {
            $feeSchedule->delete();
        }
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.feeScheduleSlots.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "tuitionFeeSheduleSlotMangement",
                "authAdmin" => $authAdmin,
                "data" => $feeSchedule,
                "message" => "Tuition Fee Schedule Slots Deleted",
            ]
        );
        StudentActionEvent::dispatch([
            'schoolBranch' => $currentSchool->id,
            'specialtyIds'   => [$feeSchedule->specialty_id],
            'feature'      => 'tuitionFeeScheduleDelete',
            'message'      => 'Tuition Fee Schedule Deleted',
            'data'         => $feeSchedule,
        ]);
    }
    /**
     * The schedule's installments in due date order. Each carries the amount
     * due for the specialty's tuition; a student's own amounts follow their
     * tuition fee total (see TuitionFeeProgressService).
     */
    public function getFeeScheduleSlots($feeScheduleId, $currentSchool)
    {
        $feeSchedule = $this->feeSchedule($currentSchool, $feeScheduleId);
        $tuition = (float) $feeSchedule->schoolYear->specialty->school_fee;

        return FeeScheduleSlot::where("school_branch_id", $currentSchool->id)
            ->where("fee_schedule_id", $feeSchedule->id)
            ->with(['installment'])
            ->orderBy('due_date')
            ->get()
            ->map(fn(FeeScheduleSlot $slot) => [
                ...$slot->toArray(),
                'fee_percentage' => (float) $slot->percentage,
                'amount' => round($tuition * ((float) $slot->percentage / 100), 2),
            ]);
    }

    private function feeSchedule(object $currentSchool, string $feeScheduleId): FeeSchedule
    {
        return FeeSchedule::where('school_branch_id', $currentSchool->id)
            ->with(['schoolYear.specialty.level', 'schoolYear.systemAcademicYear'])
            ->findOrFail($feeScheduleId);
    }
}
