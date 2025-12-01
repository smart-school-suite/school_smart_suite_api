<?php

namespace App\Services\RegistrationFee;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\RegistrationFeeTransactions;
use App\Models\RegistrationFee;
use Exception;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;

class RegistrationFeeTransactionService
{
    public function bulkDeleteRegistrationFeeTransactions($transactionIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = RegistrationFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->findOrFail($transactionId['transaction_id']);
                $transaction->delete();
                $result[] = $transaction;
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.registrationFee.delete.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "registrationFeeManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Registration Fee Transaction Deleted",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function reverseRegistrationFeePaymentTransaction(string $transactionId, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $transaction = RegistrationFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['registrationFee'])
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
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.registrationFee.reverse.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "registrationFeeManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $transaction,
                    "message" => "Registration Fee Payment Transaction Reversed",
                ]
            );
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            throw new Exception('Database error: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getRegistrationFeeTransactions($currentSchool)
    {
        try {
            $transactions = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['registrationFee.student', 'registrationFee.level', 'registrationFee.specialty'])
                ->get();

            if ($transactions->isEmpty()) {
                throw new AppException(
                    "No registration fee transactions were found for this school branch.",
                    404,
                    "No Transactions Found",
                    "There are no registration fee transaction records available in the system for your school branch.",
                    null
                );
            }

            return $transactions;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving registration fee transactions.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of transactions from being retrieved successfully.",
                null
            );
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
            ->findorFail($transactionId);
        $transaction->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" => ["schoolAdmin.registrationFee.delete.transaction"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature" => "registrationFeeManagement",
                "authAdmin" => $authAdmin,
                "data" => $transaction,
                "message" => "Registration Fee Transaction Deleted",
            ]
        );
        return true;
    }
    public function bulkReverseRegistrationFeeTransaction($transactionIds, $currentSchool, $authAdmin)
    {
        $result = [];
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
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" => ["schoolAdmin.registrationFee.reverse.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" => $currentSchool->id,
                    "feature" => "registrationFeeManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Registration Fee Payment Transaction Reversed",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
