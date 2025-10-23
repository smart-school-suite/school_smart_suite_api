<?php

namespace App\Services\AdditionalFee;

use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeStatJob;
use App\Models\AdditionalFees;
use App\Models\Student;
use App\Notifications\AdditionalFee;
use Illuminate\Support\Str;
use Exception;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdditionalFeeService
{
    public function createStudentAdditionalFees(array $additionalFees, $currentSchool)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->find($additionalFees['student_id']);
        $studentAdditionFees = new AdditionalFees();
        $additionalFeeId = Str::uuid();
        $studentAdditionFees->id = $additionalFeeId;
        $studentAdditionFees->reason = $additionalFees['reason'];
        $studentAdditionFees->amount = $additionalFees['amount'];
        $studentAdditionFees->status = "unpaid";
        $studentAdditionFees->additionalfee_category_id = $additionalFees['additionalfee_category_id'];
        $studentAdditionFees->school_branch_id = $currentSchool->id;
        $studentAdditionFees->specialty_id = $student->specialty_id;
        $studentAdditionFees->level_id = $student->level_id;
        $studentAdditionFees->student_id = $student->id;
        $studentAdditionFees->save();
        AdditionalFeeStatJob::dispatch($additionalFeeId, $currentSchool->id);
        $student->notify(new AdditionalFee($additionalFees['amount'], $additionalFees['reason']));
        return $studentAdditionFees;
    }
    public function deleteStudentAdditionalFees(string $feeId, $currentSchool)
    {
        $additionalFee = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);

        if (!$additionalFee) {
            throw new AppException(
                "Student Additional Fee record ID '{$feeId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Fee Record Not Found 🗑️",
                "We couldn't find the specific additional fee record you are trying to delete. It may have already been removed or the ID is incorrect.",
                null
            );
        }

        try {
            $additionalFee->delete();
            return $additionalFee;
        } catch (Exception $e) {
            throw new AppException(
                "Failed to delete Student Additional Fee record ID '{$feeId}'. Error: " . $e->getMessage(),
                500,
                "Deletion Failed Due to System Error 🛑",
                "We were unable to remove the fee record due to a system error. Please try again or contact support.",
                null
            );
        }
    }
    public function updateStudentAdditionalFees(array $additionalFeesData, string $feeId, $currentSchool)
    {
        $additionalFee = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);

        if (!$additionalFee) {
            throw new AppException(
                "Student Additional Fee record ID '{$feeId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Fee Record Not Found 🔎",
                "We couldn't find the specific additional fee record you are trying to update. It may have already been deleted or the ID is incorrect.", // Detailed User Message
                null
            );
        }

        $removedEmptyInputs = array_filter($additionalFeesData);

        if (empty($removedEmptyInputs)) {
            throw new AppException(
                "Attempted update on fee ID '{$feeId}' with empty data.",
                400,
                "No Data Provided for Update 📝",
                "Please provide the specific fields and values you wish to update for this additional fee record.",
                null
            );
        }

        try {
            $additionalFee->update($removedEmptyInputs);
            return $additionalFee;
        } catch (Exception $e) {
            throw new AppException(
                "Failed to update Student Additional Fee record ID '{$feeId}'. Error: " . $e->getMessage(),
                500,
                "Update Failed Due to System Error 🛑",
                "We were unable to save the changes to the fee record due to a system error. Please try again or contact support.",
                null
            );
        }
    }
    public function getStudentAdditionalFees(string $studentId, $currentSchool)
    {
        try {
            $studentAdditionFees = AdditionalFees::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $studentId)
                ->with(['student', 'specialty', 'level', 'feeCategory'])
                ->get();

            if ($studentAdditionFees->isEmpty()) {
                throw new AppException(
                    "No additional fees were found for this student.",
                    404,
                    "No Additional Fees Found",
                    "There are no additional fee records available for the specified student in the system.",
                    null
                );
            }

            return $studentAdditionFees;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving student additional fees.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the additional fee data from being retrieved successfully.",
                null
            );
        }
    }
    public function getAdditionalFeeDetails($currentSchool, string $feeId)
    {
        $additionalFeeDetails = AdditionalFees::where("school_branch_id", $currentSchool->id)
            ->with(['student', 'specialty', 'level', 'feeCategory'])
            ->find($feeId);

        if (!$additionalFeeDetails) {
            throw new AppException(
                "Additional Fee record ID '{$feeId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Fee Details Not Found 🔎",
                "We couldn't find the detailed record for the additional fee you requested. Please verify the Fee ID and ensure it belongs to a student at your school branch.", // Detailed User Message
                null
            );
        }
        return $additionalFeeDetails;
    }
    public function getAdditionalFees($currentSchool)
    {
        try {
            $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)
                ->with(['student', 'specialty', 'level', 'feeCategory'])
                ->get();

            if ($additionalFees->isEmpty()) {
                throw new AppException(
                    "No additional fees were found for this school branch.",
                    404,
                    "No Additional Fees Found",
                    "There are no additional fee records available in the system for your school branch.",
                    null
                );
            }

            return $additionalFees;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving additional fees.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of additional fees from being retrieved successfully.",
                null
            );
        }
    }
    public function bulkUpdateStudentAdditionalFees($additionalFees, $currentSchool)
    {
        if (empty($additionalFees)) {
            throw new AppException(
                "No fee data was provided for bulk update.",
                400,
                "No Data Provided for Update 📝",
                "Please provide a list of additional fee records you wish to update.",
                null
            );
        }

        $successfulUpdates = [];
        $failedUpdates = [];
        $updateAttempts = collect($additionalFees);

        $updates = $updateAttempts
            ->filter(fn($fee) => isset($fee['fee_id']) && $fee['fee_id'] !== null)
            ->map(function ($fee) {
                return collect($fee)->filter(fn($value) => !is_null($value));
            });

        $feeIds = $updates->pluck('fee_id')->toArray();
        $nonExistentIds = [];

        DB::beginTransaction();

        try {
            $existingFees = AdditionalFees::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $feeIds)
                ->get()
                ->keyBy('id');

            foreach ($feeIds as $id) {
                if (!$existingFees->has($id)) {
                    $nonExistentIds[] = $id;
                }
            }

            $updates->each(function ($data) use ($existingFees, &$successfulUpdates, &$failedUpdates) {
                $feeId = $data['fee_id'];

                if (!$existingFees->has($feeId)) {
                    return;
                }

                $fee = $existingFees->get($feeId);
                $updateData = $data->except('fee_id');
                $originalAmount = $fee->amount;

                if ($fee->status === 'paid' && $updateData->has('amount')) {
                    if (bccomp($originalAmount, $updateData['amount'], 2) !== 0) {
                        $failedUpdates[] = [
                            'id' => $feeId,
                            'reason' => "Cannot update 'amount' because the fee is already paid (Current Amount: {$originalAmount}).",
                            'status' => 'paid',
                        ];
                        $updateData = $updateData->except('amount');
                    }
                }

                if ($updateData->isNotEmpty()) {
                    $fee->update($updateData->toArray());
                    $successfulUpdates[] = $feeId;
                } elseif (in_array($feeId, array_column($failedUpdates, 'id'))) {
                } else {
                    $failedUpdates[] = [
                        'id' => $feeId,
                        'reason' => "No valid fields were provided for update after filtering empty and restricted ('amount') fields.",
                    ];
                }
            });

            if (!empty($nonExistentIds) || !empty($failedUpdates)) {
                DB::rollBack();

                $errorMessages = [];
                if (!empty($nonExistentIds)) {
                    $errorMessages[] = "The following fee IDs were not found at your school branch: " . implode(', ', $nonExistentIds);
                }
                if (!empty($failedUpdates)) {
                    $failedList = collect($failedUpdates)->map(fn($f) => "ID {$f['id']}: {$f['reason']}")->implode('; ');
                    $errorMessages[] = "The following updates failed due to validation rules (e.g., attempting to change amount on a paid fee): {$failedList}";
                }

                throw new AppException(
                    "Bulk update failed due to validation errors or missing IDs.",
                    400,
                    "Bulk Update Failed 🛑",
                    "Some of the fee records could not be updated. The entire batch was rolled back. Details: " . implode(' | ', $errorMessages),
                    null
                );
            }

            DB::commit();

            // Return the IDs of records that were successfully updated
            return $successfulUpdates;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "A critical error occurred during the bulk update process: " . $e->getMessage(),
                500,
                "System Update Failed 🚨",
                "We were unable to complete the bulk update due to an unexpected system error. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
}
