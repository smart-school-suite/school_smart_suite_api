<?php

namespace App\Services\RegistrationFee;

use Illuminate\Support\Facades\DB;
use App\Models\RegistrationFee;
use Exception;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\AppException;

class RegistrationFeeService
{
    public function getRegistrationFees($currentSchool)
    {
        try {
            $registrationFees = RegistrationFee::where("school_branch_id", $currentSchool->id)
                ->with(['student', 'specialty', 'level'])
                ->get();

            if ($registrationFees->isEmpty()) {
                throw new AppException(
                    "No registration fees were found for this school branch.",
                    404,
                    "No Registration Fees Found",
                    "There are no student registration fee records available in the system for your school branch.",
                    null
                );
            }

            return $registrationFees;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving registration fee records.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the registration fee data from being retrieved successfully.",
                null
            );
        }
    }
    public function bulkDeleteRegistrationFee($feeIds, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $registrationFees = RegistrationFee::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $feeIds)
                ->get();

            if ($registrationFees->count() !== count($feeIds)) {
                throw new Exception("Some registration fees were not found.", 404);
            }

            foreach ($registrationFees as $fee) {
                if ($fee->status === "not paid") {
                    throw new Exception("Some registration fees are unpaid and cannot be deleted.", 400);
                }
            }

            RegistrationFee::whereIn('id', $feeIds)->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function deleteRegistrationFee($feeId, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $registrationFee = RegistrationFee::where('school_branch_id', $currentSchool->id)
                ->where('id', $feeId)
                ->firstOrFail();

            if ($registrationFee->status !== "paid") {
                throw new Exception("This registration fee has not been paid and cannot be deleted.", 400);
            }

            $registrationFee->delete();

            DB::commit();

            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new Exception("Registration fee not found.", 404);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getRegistrationFeeDetails($currentSchool, $registrationFeeId)
    {
        try {
            $registrationFee = RegistrationFee::where('school_branch_id', $currentSchool->id)
                ->find($registrationFeeId);

            if (!$registrationFee) {
                throw new AppException(
                    "Registration Fee ID '{$registrationFeeId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Registration Fee Not Found 🔎",
                    "The registration fee record you requested could not be found at your school branch. Please verify the ID.",
                    null
                );
            }

            return $registrationFee;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve registration fee details. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the registration fee details. Please try again or contact support.",
                null
            );
        }
    }
}
