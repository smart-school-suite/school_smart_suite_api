<?php

namespace App\Services\TuitionFee;

use Throwable;
use App\Exceptions\AppException;
use App\Models\Installment;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TuitionFeeInstallmentService
{
    public function createInstallment($installmentData)
    {
        try {
            if (empty($installmentData)) {
                throw new AppException(
                    "No data provided for creating the installment.",
                    400,
                    "No Data Provided 📝",
                    "You must provide valid data to create a new installment plan.",
                    null
                );
            }

            $installment = Installment::create($installmentData);
            return $installment;
        } catch (AppException $e) {
            throw $e;
        } catch (QueryException $e) {
            throw new AppException(
                "Database error creating installment. Check for duplicate or missing required fields. Error: " . $e->getMessage(),
                409,
                "Installment Creation Failed 🛑",
                "A database constraint failed. Ensure all data is valid and unique where required (e.g., name).",
                null
            );
        } catch (Throwable $e) {
            throw new AppException(
                "An unexpected error occurred while creating the installment. Error: " . $e->getMessage(),
                500,
                "System Error 🚨",
                "We were unable to process the installment creation. Please try again or contact support.",
                null
            );
        }
    }
    public function updateInstallment($installmentData, $installmentId)
    {
        try {
            $installment = Installment::findOrFail($installmentId);
            $cleanedData = array_filter($installmentData);

            if (empty($cleanedData)) {
                throw new AppException(
                    "No valid data provided for updating installment ID '{$installmentId}'.",
                    400,
                    "No Data Provided 📝",
                    "You must provide at least one field with a valid value to update the installment.",
                    null
                );
            }

            $installment->update($cleanedData);
            return $installment;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Installment ID '{$installmentId}' not found.",
                404,
                "Installment Not Found 🔎",
                "The installment plan you are trying to update could not be found.",
                null
            );
        } catch (QueryException $e) {
            throw new AppException(
                "Database error updating installment ID '{$installmentId}'. Check for duplicate or invalid data. Error: " . $e->getMessage(),
                409,
                "Update Failed 📛",
                "A database constraint failed. Ensure the updated data (e.g., name) is valid and unique.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "An unexpected error occurred while updating installment ID '{$installmentId}'. Error: " . $e->getMessage(),
                500,
                "System Error 🚨",
                "We were unable to process the update. Please try again or contact support.",
                null
            );
        }
    }
    public function getInstallments()
    {
        try {
            $installments = Installment::all();

            if ($installments->isEmpty()) {
                throw new AppException(
                    "No installment plans were found in the system.",
                    404,
                    "No Installments Found 📃",
                    "There are currently no fee installment plans defined.",
                    null
                );
            }

            return $installments;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve installment plans. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the installments. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteInstallment($installmentId)
    {
        $installmentName = 'Unknown';
        try {
            $installment = Installment::findOrFail($installmentId);
            $installmentName = $installment->name ?? $installmentId;

            $installment->delete();

            return $installment;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Installment ID '{$installmentId}' not found for deletion.",
                404,
                "Installment Not Found 🗑️",
                "The installment plan you are trying to delete could not be found.",
                null
            );
        } catch (Throwable $e) {
            $message = "Failed to delete installment '{$installmentName}' (ID: {$installmentId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409, // Conflict
                    "Deletion Blocked 🔒",
                    "Cannot delete the installment because it is currently linked to active fee structures or student records. Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred during deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function deactivateInstallment($installmentId)
    {
        try {
            $installment = Installment::findOrFail($installmentId);

            $installment->status = "inactive";
            $installment->save();

            return $installment;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Installment ID '{$installmentId}' not found for deactivation.",
                404,
                "Installment Not Found 🔎",
                "The installment plan you are trying to deactivate could not be found.",
                null
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to deactivate installment ID '{$installmentId}'. Error: " . $e->getMessage(),
                500,
                "Deactivation Failed 🛑",
                "A system error occurred while changing the installment status. Please try again or contact support.",
                null
            );
        }
    }
    public function activateInstallment($installmentId)
    {
        try {
            $installment = Installment::findOrFail($installmentId);

            $installment->status = "active";
            $installment->save();

            return $installment;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Installment ID '{$installmentId}' not found for activation.",
                404,
                "Installment Not Found 🔎",
                "The installment plan you are trying to activate could not be found.",
                null
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to activate installment ID '{$installmentId}'. Error: " . $e->getMessage(),
                500,
                "Activation Failed 🛑",
                "A system error occurred while changing the installment status. Please try again or contact support.",
                null
            );
        }
    }
    public function getActiveFeeInstallment()
    {
        try {
            $installments = Installment::where("status", "active")->get();

            if ($installments->isEmpty()) {
                throw new AppException(
                    "No active fee installment plans were found in the system.",
                    404,
                    "No Active Installments Found 📃",
                    "There are currently no active fee installment plans defined.",
                    null
                );
            }

            return $installments;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve active fee installment plans. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the active installments. Please try again or contact support.",
                null
            );
        }
    }
}
