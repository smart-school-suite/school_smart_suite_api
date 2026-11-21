<?php

namespace App\Services\RegistrationFee;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\RegistrationFeeTransactions;
use App\Models\RegistrationFee;
use Exception;
use App\Exceptions\AppException;

class RegistrationFeeTransactionService
{
    public function bulkDeleteRegistrationFeeTransactions(array $transactionIds, object $currentSchool, object $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = RegistrationFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->with(['registrationFee.specialty'])
                    ->findOrFail($transactionId['transaction_id']);
                $transaction->delete();
                $result[] = $transaction;
                $studentIds[] = $transaction->registrationFee->student_id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function reverseRegistrationFeePaymentTransaction(string $transactionId, object $currentSchool, object $authAdmin)
    {
        try {
            DB::beginTransaction();
            $transaction = RegistrationFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['registrationFee.specialty'])
                ->find($transactionId);

            if (!$transaction) {
                throw new Exception("Transaction  record not found", 404);
            }
            $registrationFees = RegistrationFee::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $transaction->registrationFee->student_id)
                ->where("level_id", $transaction->registrationFee->level_id)
                ->where("specialty_id", $transaction->registrationFee->specialty_id)
                ->where("id", $transaction->registrationfee_id)
                ->first();

            if (!$registrationFees) {
                throw new Exception("Registration fees record not found", 404);
            }
            $registrationFees->status = 'not paid';
            $registrationFees->save();
            $transaction->delete();
            DB::commit();
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            throw new Exception('Database error: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * The branch's registration fee payments, with their fee's academic year.
     * An empty list is a normal answer (nothing paid yet), not an error; an
     * unexpected failure keeps its cause, so Sentry shows what went wrong.
     */
    public function getRegistrationFeeTransactions(object $currentSchool)
    {
        try {
            return RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['registrationFee.student', 'registrationFee.specialty.level', 'registrationFee.schoolAcademicYear.systemAcademicYear'])
                ->get();
        } catch (\Throwable $e) {
            throw AppException::unexpected('load the registration fee transactions', $e);
        }
    }
    public function getRegistrationFeeTransactionDetails($currentSchool, $transactionId)
    {
        $transactionDetails = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['registrationFee.student', 'registrationFee.level', 'registrationFee.specialty'])
            ->find($transactionId);
        return $transactionDetails;
    }
    public function deleteRegistrationFeeTransaction($currentSchool, $transactionId, $authAdmin)
    {
        $transaction = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['registrationFee'])
            ->findorFail($transactionId);
        $transaction->delete();

        return true;
    }
    public function bulkReverseRegistrationFeeTransaction($transactionIds, $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds  = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = RegistrationFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->with(['registrationFee'])
                    ->find($transactionId['transaction_id']);

                if (!$transaction) {
                    throw new Exception("Transaction  record not found", 404);
                }
                $registrationFees = RegistrationFee::where("school_branch_id", $currentSchool->id)
                    ->where("student_id", $transaction->registrationFee->student_id)
                    ->where("level_id", $transaction->registrationFee->level_id)
                    ->where("specialty_id", $transaction->registrationFee->specialty_id)
                    ->where("id", $transaction->registrationfee_id)
                    ->first();

                if (!$registrationFees) {
                    throw new Exception("Registration fees record not found", 404);
                }
                $registrationFees->status = 'not paid';
                $registrationFees->save();
                $transaction->delete();
                $result[] = [
                    $transaction
                ];
                $studentIds[] = $transaction->registrationFee->student_id;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
