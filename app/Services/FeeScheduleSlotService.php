<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateStudentFeeScheduleJob;
use App\Jobs\DataCreationJob\UpdateStudentFeeScheduleJob;
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
class FeeScheduleSlotService
{

    /**
     * Creates fee schedule slots for a given fee schedule.
     *
     * @param Schoolbranches $currentSchool The school branch the fee schedule belongs to.
     * @param string $feeScheduleId The ID of the fee schedule to add slots to.
     * @param array $slotsData An array of slot data, each containing 'due_date', 'fee_percentage', 'installment_id'.
     * @throws ModelNotFoundException If the fee schedule is not found for the given school branch.
     * @throws \Exception If an error occurs during slot creation.
     * @return bool True if slots were created successfully.
     */
    public function createFeeScheduleSlots(Schoolbranches $currentSchool, string $feeScheduleId, array $slotsData): bool
    {
        DB::beginTransaction();
        try {

            $feeSchedule = FeeSchedule::where('school_branch_id', $currentSchool->id)
                                      ->with(['specialty', 'level', 'schoolSemester.semester'])
                                      ->findOrFail($feeScheduleId);
            $scheduleData = [
                'schoolYear' => $feeSchedule->schoolSemester->school_year,
                'specialty' => $feeSchedule->specialty->specialty_name,
                'level' => $feeSchedule->level->level,
                'semester' => $feeSchedule->schoolSemester->semester->name
            ];
            if (!$feeSchedule->specialty || !isset($feeSchedule->specialty->school_fee)) {
                throw new \InvalidArgumentException('Fee Schedule or associated Specialty/School Fee data is incomplete.');
            }

            $baseSchoolFee = floatval($feeSchedule->specialty->school_fee);

            $slotsToCreate = [];

            foreach ($slotsData as $index => $slotData) {

                if (!isset($slotData['due_date'], $slotData['fee_percentage'], $slotData['installment_id'])) {
                    throw new \InvalidArgumentException("Missing required data for slot #{$index}. Required: due_date, fee_percentage, installment_id.");
                }

                if (!is_numeric($slotData['fee_percentage'])) {
                    throw new \InvalidArgumentException("Invalid fee_percentage for slot #{$index}. Must be numeric.");
                }

                $percentage = floatval($slotData['fee_percentage']);

                $amount = round($baseSchoolFee * ($percentage / 100), 2);

                $slotsToCreate[] = [
                    'id'               => (string) Str::uuid(),
                    'due_date'         => $slotData['due_date'],
                    'fee_percentage'   => $percentage,
                    'amount'           => $amount,
                    'installment_id'   => $slotData['installment_id'],
                    'fee_schedule_id'  => $feeScheduleId,
                    'school_branch_id' => $currentSchool->id,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }

            FeeScheduleSlot::insert($slotsToCreate);
             $feeSchedule->update([
                'config_status' => 'configured'
             ]);
            DB::commit();
            CreateStudentFeeScheduleJob::dispatch($feeScheduleId, $currentSchool->id,  $feeSchedule->specialty->id);
            SendAdminFeeScheduleNotificationJob::dispatch(
                $currentSchool->id,
                $scheduleData
            );
            SendFeeScheduleNotificationJob::dispatch(
               $currentSchool->id,
                $feeSchedule->specialty->id,
                $scheduleData
            );
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

    /**
     * Updates fee schedule slots for a given fee schedule.
     *
     * @param Schoolbranches $currentSchool The school branch instance.
     * @param string $feeScheduleId The ID of the fee schedule to update.
     * @param array $slotsData An array of slot data, each containing 'id', and optionally 'due_date', 'fee_percentage', 'installment_id'.
     * @return bool True if the update was successful, false otherwise.
     *
     * @throws ModelNotFoundException If the fee schedule or any slot is not found.
     * @throws \InvalidArgumentException If essential data for calculations is missing.
     * @throws \Exception On other unexpected errors during the update process.
     */
    public function updateFeeScheduleSlots(Schoolbranches $currentSchool, string $feeScheduleId, array $slotsData): bool
    {
        DB::beginTransaction();

        try {

            $feeSchedule = FeeSchedule::where('school_branch_id', $currentSchool->id)
                                    ->with('specialty')
                                    ->findOrFail($feeScheduleId);

            if (!$feeSchedule->specialty || !isset($feeSchedule->specialty->school_fee)) {
                throw new \InvalidArgumentException('Fee Schedule or associated Specialty/School Fee data is incomplete. Cannot determine base amount for calculations.');
            }

            $baseSchoolFee = (float) $feeSchedule->specialty->school_fee;


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

                    $amount = round($baseSchoolFee * ($percentage / 100), 2);

                    $updatePayload['fee_percentage'] = $percentage;
                    $updatePayload['amount'] = $amount;
                }

                if (isset($slotData['installment_id']) && !is_null($slotData['installment_id'])) {
                    $updatePayload['installment_id'] = $slotData['installment_id'];
                }

                if (!empty($updatePayload)) {
                    $slot->update($updatePayload);
                }
            }

            DB::commit();

            //review this worker its incomplete
            UpdateStudentFeeScheduleJob::dispatch($feeScheduleId, $currentSchool->id,  $feeSchedule->specialty->id);
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
    public function deleteFeeScheduleSlot(string $feeScheduleId, $currentSchool){
        $feeSchedules = FeeSchedule::where("school_branch_id", $currentSchool->id)
                                    ->where("fee_schedule_id", $feeScheduleId)
                                    ->get();
        foreach($feeSchedules as $feeSchedule){
            $feeSchedule->delete();
        }

    }
    public function getFeeScheduleSlots($feeScheduleId, $currentSchool){
        $getFeeScheduleSlots = FeeScheduleSlot::where("school_branch_id", $currentSchool->id)
                                               ->where("fee_schedule_id", $feeScheduleId)
                                               ->with(['installment'])
                                               ->get();
        return $getFeeScheduleSlots;

    }
}
