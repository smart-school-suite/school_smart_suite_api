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
    /**
     * The branch's registration fee records. An empty list is a normal
     * answer (no student admitted yet), not an error. An unexpected failure
     * keeps its cause, so Sentry shows what went wrong.
     */
    public function getRegistrationFees(object $currentSchool)
    {
        try {
            return RegistrationFee::where("school_branch_id", $currentSchool->id)
                ->with(['student', 'specialty.level', 'schoolAcademicYear.systemAcademicYear'])
                ->get();
        } catch (Throwable $e) {
            throw AppException::unexpected('load the registration fees', $e);
        }
    }
    public function bulkDeleteRegistrationFee(array $feeIds, object $currentSchool, object $authAdmin)
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
    public function deleteRegistrationFee(string $feeId, object $currentSchool, object $authAdmin)
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
    public function getRegistrationFeeDetails(object $currentSchool, string $registrationFeeId)
    {
        try {
            $registrationFee = RegistrationFee::where('school_branch_id', $currentSchool->id)
            ->with(['student', 'specialty', 'level'])
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

    public function getStudentRegistratonFees(object $currentSchool, object $student)
    {
        $registrationFees = RegistrationFee::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->with(['specialty', 'level'])
            ->get();
        return $registrationFees;
    }
}
