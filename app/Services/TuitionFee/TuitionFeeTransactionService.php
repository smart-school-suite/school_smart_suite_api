<?php

namespace App\Services\TuitionFee;

use App\Models\TuitionFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\TuitionFeeTransactions;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;
use App\Exceptions\AppException;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;

class TuitionFeeTransactionService
{
    public function bulkDeleteTuitionFeeTransaction($transactionIds, $currentSchool, $authAdmin)
    {
        $result = [];
        $studentIds = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transactions = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
                    ->with(['tuition'])->findOrFail($transactionId['transaction_id']);
                $transactions->delete();
                $result[] = $transactions;
                $studentIds[] = $transactions->tuition->student_id;
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.tuitionFee.delete.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeTransactionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "Tuition Fee Transaction Deleted Event",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => $studentIds,
                'feature'      => 'tuitionFeeTransactionDeleted',
                'message'      => 'Tuition Fees Transaction Deleted',
                'data'         => $result,
            ]);
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReverseTuitionFeeTransaction($transactionIds, $currentSchool, $authAdmin)
    {
        $results = [];
        $studentIds = [];
        try {
            foreach ($transactionIds as $transactionId) {
                $transaction = TuitionFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->with(['tuition'])
                    ->find($transactionId['transaction_id']);

                if (!$transaction) {
                    throw new Exception("Payment record not found", 404);
                }
                $studentTuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)
                    ->where("student_id", $transaction->tuition->student_id)
                    ->where("level_id", $transaction->tuition->level_id)
                    ->where("specialty_id", $transaction->tuition->specialty_id)
                    ->first();

                if (!$studentTuitionFees) {
                    throw new Exception("Tuition fees record not found", 404);
                }
                $studentTuitionFees->amount_paid -= $transaction->amount;
                $studentTuitionFees->amount_left += $transaction->amount;
                if ($studentTuitionFees->amount_left > 0) {
                    $studentTuitionFees->status = "owing";
                } else {
                    $studentTuitionFees->status = "completed";
                }

                $studentTuitionFees->save();
                $transaction->delete();
                $results[] = [
                    $transaction
                ];
                $studentIds[] = [
                    $transaction->tuition->student_id
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.tuitionFee.reverse.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeTransactionManagement",
                    "action" => "tuitionFeeTransaction.reversed",
                    "authAdmin" => $authAdmin,
                    "data" => $results,
                    "message" => "Tuition Fee Transaction Reversed",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => $studentIds,
                'feature'      => 'tuitionFeeTransactionReversed',
                'message'      => 'Tuition Fee Transaction Reversed',
                'data'         => $results,
            ]);
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getTuitionFeeTransactions($currentSchool)
    {
        try {
            $getTuitionFeeTransactions = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['tuition.student', 'tuition.specialty', 'tuition.level'])
                ->get();

            if ($getTuitionFeeTransactions->isEmpty()) {
                throw new AppException(
                    "No tuition fee transactions were found for this school branch.",
                    404,
                    "No Transactions Found",
                    "There are no tuition fee transaction records available in the system for your school branch.",
                    null
                );
            }

            return $getTuitionFeeTransactions;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving tuition fee transactions.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of transactions from being retrieved successfully.",
                null
            );
        }
    }
    public function deleteTuitionFeeTransaction($transactionId, $currentSchool, $authAdmin)
    {
        try {
            $transaction = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['tuition'])->find($transactionId);

            if (!$transaction) {
                throw new AppException(
                    "Tuition fee transaction ID '{$transactionId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Transaction Not Found 🔎",
                    "The transaction you are attempting to delete could not be located.",
                    null
                );
            }

            $transaction->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.tuitionFee.delete.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeTransactionManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $transaction,
                    "message" => "Tuition Fee Transaction Deleted",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$transaction->tuition->student_id],
                'feature'      => 'tuitionFeeTransactionDeleted',
                'message'      => 'Tuition Fee Transaction Deleted',
                'data'         => $transaction,
            ]);
            return $transaction;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            $message = "Failed to delete tuition fee transaction ID '{$transactionId}'. Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the transaction as it may be linked to other dependent records. Please check the data integrity.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred during transaction deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function tuitionFeeTransactionDetails($transactionId, $currentSchool)
    {
        try {
            $transactionDetail = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['tuition', 'tuition.specialty', 'tuition.level', 'tuition.student'])
                ->find($transactionId);

            if (!$transactionDetail) {
                throw new AppException(
                    "Tuition fee transaction ID '{$transactionId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Transaction Not Found 🔎",
                    "The detailed record for the requested tuition fee transaction could not be located.",
                    null
                );
            }

            return $transactionDetail;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve tuition fee transaction details for ID '{$transactionId}'. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the transaction details. Please try again or contact support.",
                null
            );
        }
    }
    public function reverseFeePaymentTransaction(string $transactionId, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();

        try {
            $transaction = TuitionFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['tuition'])
                ->find($transactionId);

            if (!$transaction) {
                throw new AppException(
                    "Payment transaction ID '{$transactionId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Transaction Not Found 🔎",
                    "The specific payment record you are trying to reverse could not be found.",
                    null
                );
            }

            $reversalAmount = $transaction->amount;
            $tuitionId = $transaction->tuition_id;

            $studentTuitionFees = $transaction->tuition;

            if (!$studentTuitionFees) {
                throw new AppException(
                    "Associated Tuition Fees record (ID: {$tuitionId}) not found for transaction ID '{$transactionId}'.",
                    404,
                    "Associated Record Missing ⚠️",
                    "The primary tuition fee record linked to this payment is missing.",
                    null
                );
            }

            if (bccomp($studentTuitionFees->amount_paid, $reversalAmount, 2) < 0) {
                throw new AppException(
                    "Reversal amount ({$reversalAmount}) exceeds the current total amount paid on the tuition record.",
                    400,
                    "Reversal Error 🛑",
                    "The transaction cannot be reversed as it would result in a negative amount paid. Data inconsistency detected.",
                    null
                );
            }

            $studentTuitionFees->amount_paid -= $reversalAmount;
            $studentTuitionFees->amount_left += $reversalAmount;

            if (bccomp($studentTuitionFees->amount_left, 0, 2) > 0) {
                $studentTuitionFees->status = "owing";
            } else {
                $studentTuitionFees->status = "completed";
                $studentTuitionFees->amount_left = 0;
            }

            $studentTuitionFees->save();

            $transaction->delete();

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.tuitionFee.reverse.transaction"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "tuitionFeeTransactionManagement",
                    "action" => "tuitionFeeTransaction.reversed",
                    "authAdmin" => $authAdmin,
                    "data" => $transaction,
                    "message" => "Tuition Fee Transaction Reversed",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$transaction->tuition->student_id],
                'feature'      => 'tuitionFeeTransactionReversed',
                'message'      => 'Tuition Fee Transaction Reversed',
                'data'         => $transaction,
            ]);
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Database error during fee payment reversal: " . $e->getMessage(), ['exception' => $e]);
            throw new AppException(
                'A database error occurred during the reversal process.',
                500,
                "Database Error 🚨",
                "We encountered a critical error while reversing the payment. The transaction has been cancelled.",
                null
            );
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "An unexpected system error occurred during the fee payment reversal: " . $e->getMessage(),
                500,
                "Reversal System Failure 🛑",
                "An unknown system error prevented the reversal from being processed. Please contact support.",
                null
            );
        }
    }
}
