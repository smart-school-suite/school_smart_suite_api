<?php

namespace App\Services\AdditionalFee;

use App\Jobs\NotificationJobs\SendAdditionalFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendAdminAdditionalFeeNotificationJob;
use App\Models\AdditionalFees;
use App\Models\AdditionalFeeTransactions;
use App\Notifications\AdditionalFeePaidNotification;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;
use App\Events\Analytics\FinancialAnalyticsEvent;
use App\Constant\Analytics\Financial\FinancialAnalyticsEvent as FinancialEventConstant;

class AdditionalFeePaymentService
{
    public function payAdditionalFees(array $additionalFeesData, $currentSchool, $authAdmin): AdditionalFeeTransactions
    {
        DB::beginTransaction();

        try {
            $feeId = $additionalFeesData['fee_id'];
            $amountPaid = $additionalFeesData['amount'];

            $additionalFee = AdditionalFees::where('school_branch_id', $currentSchool->id)
                ->with(['feeCategory', 'student', 'specialty'])
                ->find($feeId);

            if (!$additionalFee) {
                DB::rollBack();
                throw new AppException(
                    "Student additional fee record ID '{$feeId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Fee Record Not Found 🔎",
                    "The specific additional fee record you are trying to pay could not be found. Please verify the ID.",
                    null
                );
            }

            if ($additionalFee->status === 'paid') {
                DB::rollBack();
                throw new AppException(
                    "Student additional fee record ID '{$feeId}' is already marked as paid.",
                    409,
                    "Fee Already Paid ✅",
                    "This additional fee is already marked as paid and cannot accept further payments.",
                    null
                );
            }

            if (bccomp($amountPaid, $additionalFee->amount, 2) > 0) {
                DB::rollBack();
                throw new AppException(
                    "Amount paid ('{$amountPaid}') exceeds the total amount owed ('{$additionalFee->amount}') for this additional fee.",
                    400,
                    "Overpayment Not Allowed 💰",
                    "The amount you entered for payment is greater than the total amount required for this additional fee. Please enter the exact remaining amount.",
                    null
                );
            }

            $transactionId = Str::random(10);
            $feeTransactionId = Str::uuid();

            $transaction = AdditionalFeeTransactions::create([
                'id' => $feeTransactionId,
                'transaction_id' => $transactionId,
                'amount' => $amountPaid,
                'payment_method' => $additionalFeesData['payment_method'],
                'fee_id' => $feeId,
                'school_branch_id' => $currentSchool->id,
                'additional_fee_id' => $feeId,
            ]);

            $additionalFee->status = 'paid';
            $additionalFee->save();

            DB::commit();

            if ($additionalFee->student) {
                $additionalFee->student->notify(new AdditionalFeePaidNotification(
                    $amountPaid,
                    $additionalFee->reason,
                    $additionalFee->feeCategory->title ?? 'N/A',
                    'XAF'
                ));
            }

            $adminNotificationData = [
                'student' => $additionalFee->student ?? null,
                'amount_paid' => $amountPaid,
                'fee_reason' => $additionalFee->reason,
                'fee_category_title' => $additionalFee->feeCategory->title ?? 'N/A',
                'payment_method' => $additionalFeesData['payment_method'],
            ];

            SendAdminAdditionalFeeNotificationJob::dispatch(
                $currentSchool->id,
                $adminNotificationData
            );

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFee.pay"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalFeeManagement",
                    "action" => "additionalFee.Paid",
                    "authAdmin" => $authAdmin,
                    "data" => $additionalFee,
                    "message" => "Additional Fee Paid",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$additionalFee->student_id],
                'feature'      => 'studentAdditionalFeePaid',
                'message'      => 'Student Additional Fee Paid',
                'data'         =>  $additionalFee,
            ]);

            event(new FinancialAnalyticsEvent(
                eventType: FinancialEventConstant::ADDITIONAL_FEE_PAID,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "category_id" => $additionalFee->additionalfee_category_id,
                    "amount" => $additionalFee->amount,
                    "specialty_id" => $additionalFee->specialty->id,
                    "department_id" => $additionalFee->specialty->department_id,
                    "level_id" => $additionalFee->specialty->level_id
                ]
            ));

            return $transaction;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "A system error occurred during the fee payment process: " . $e->getMessage(),
                500,
                "Payment Processing Failed 🛑",
                "We were unable to finalize the payment transaction due to a critical system error. The payment has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function reverseTransaction($transactionId, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['additionFee.specialty'])
                ->find($transactionId);

            if (!$transaction) {
                DB::rollBack();
                throw new AppException(
                    "Additional Fee Transaction ID '{$transactionId}' not found for school branch ID '{$currentSchool->id}'.",
                    404,
                    "Transaction Not Found 🔎",
                    "We couldn't locate the specific transaction record you are trying to reverse. Please ensure the Transaction ID is correct.",
                    null
                );
            }

            $additionalFees = AdditionalFees::where('id', $transaction->fee_id)
                ->where('school_branch_id', $currentSchool->id)
                ->first();

            if (!$additionalFees) {
                DB::rollBack();
                throw new AppException(
                    "Associated Additional Fee record (ID: {$transaction->fee_id}) not found for transaction ID '{$transactionId}'.",
                    404,
                    "Associated Fee Missing ⚠️",
                    "The original fee record linked to this transaction is missing from the database. Reversal cannot be completed. Please contact support.",
                    null
                );
            }

            $additionalFees->status = 'unpaid';
            $additionalFees->save();

            $transaction->delete();
            DB::commit();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFee.transactions.reverse"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalFeeTransactionManagement",
                    "action" => "additionalFeePayment.reversed",
                    "authAdmin" => $authAdmin,
                    "data" => $additionalFees,
                    "message" => "Additional Fee Transaction Reversed",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$additionalFees->student_id],
                'feature'      => 'AdditionalFeeTransactionReversed',
                'message'      => 'Student Additional Fee Paid',
                'data'         =>  $additionalFees,
            ]);
            event(new FinancialAnalyticsEvent(
                eventType: FinancialEventConstant::ADDITIONAL_FEE_REVERSED,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "category_id" => $transaction->additionFee->additionalfee_category_id,
                    "amount" => $transaction->amount,
                    "specialty_id" => $transaction->additionFee->specialty->id,
                    "department_id" => $transaction->additionFee->specialty->department_id,
                    "level_id" => $transaction->additionFee->specialty->level_id
                ]
            ));
            return $transaction;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "A critical error occurred while attempting to reverse transaction ID '{$transactionId}': " . $e->getMessage(),
                500,
                "Reversal Failed Due to System Error 🛑",
                "We were unable to complete the transaction reversal due to an unexpected system issue. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function getAdditionalFeesTransactions($currentSchool)
    {
        try {
            $getAdditionalFeesTransactions = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['additionFee.feeCategory', 'additionFee.student.specialty', 'additionFee.student.specialty.level'])
                ->get();

            if ($getAdditionalFeesTransactions->isEmpty()) {
                throw new AppException(
                    "No additional fees transactions were found for this school branch.",
                    404,
                    "No Transactions Found",
                    "There are no records of additional fee transactions available in the system for your school branch.",
                    null
                );
            }

            return $getAdditionalFeesTransactions;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving additional fee transactions.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of transactions from being retrieved successfully.",
                null
            );
        }
    }
    public function deleteTransaction($transactionId, $currentSchool, $authAdmin)
    {
        DB::beginTransaction();
        try {
            $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['additionFee.specialty'])
                ->findOrFail($transactionId);

            $transaction->delete();

            DB::commit();

            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFee.transactions.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalFeeTransactionManagement",
                    "action" => "additionalFeeTransaction.deleted",
                    "authAdmin" => $authAdmin,
                    "data" => $transaction,
                    "message" => "Additional Fee Transaction Deleted",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$transaction->additionFee->student_id],
                'feature'      => 'AdditionalFeeTransactionDelete',
                'message'      => 'Additional Fee Transaction Deleted',
                'data'         =>  $transaction,
            ]);
            return $transaction;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new AppException(
                "Additional Fee Transaction ID '{$transactionId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Transaction Not Found 🔎",
                "We couldn't locate the specific transaction record you are trying to delete. Please ensure the Transaction ID is correct.",
                null
            );
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "A critical error occurred while attempting to delete transaction ID '{$transactionId}': " . $e->getMessage(),
                500,
                "Deletion Failed Due to System Error 🛑",
                "We were unable to complete the transaction deletion due to an unexpected system issue. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function getTransactionDetail($transationId, $currentSchool)
    {
        try {
            $transactionDetials = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['additionFee', 'additionFee.feeCategory', 'additionFee.student', 'additionFee.specialty', 'additionFee.level'])
                ->findOrFail($transationId);

            return $transactionDetials;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Additional Fee Transaction ID '{$transationId}' not found for school branch ID '{$currentSchool->id}'.",
                404,
                "Transaction Details Not Found 🔎",
                "We couldn't find the details for the specific transaction you requested. Please verify the Transaction ID and ensure it belongs to your school branch.", // Detailed User Message
                null
            );
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while fetching transaction details: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "We were unable to load the transaction details due to a system error. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkDeleteTransaction($transactionIds, $currentSchool, $authAdmin)
    {
        if (empty($transactionIds)) {
            throw new AppException(
                "No transaction IDs were provided for bulk deletion.",
                400,
                "No IDs Provided 📝",
                "Please provide a list of transaction IDs you wish to delete.",
                null
            );
        }

        $successfulDeletions = [];
        $failedIds = [];
        $studentIds = [];
        $allTransactionIds = collect($transactionIds)->pluck('transaction_id')->toArray();

        try {
            DB::beginTransaction();

            foreach ($transactionIds as $item) {
                $transactionId = $item['transaction_id'];

                try {
                    $transaction = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)
                        ->with(['additionFee'])
                        ->findOrFail($transactionId);
                    $transaction->delete();

                    $successfulDeletions[] = $transactionId;
                    $studentIds[] = $transaction->additionFee->student_id;
                } catch (ModelNotFoundException $e) {
                    $failedIds[] = $transactionId;
                }
            }

            if (count($failedIds) > 0) {
                DB::rollBack();
                $notFoundList = implode(', ', $failedIds);

                throw new AppException(
                    "Bulk delete failed because the following transaction IDs were not found: {$notFoundList}",
                    404,
                    "Partial Deletion Failed ⚠️",
                    "We could not find some of the transaction records you attempted to delete. The entire operation has been cancelled. Please check the following IDs: {$notFoundList}",
                    null
                );
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFee.transactions.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalFeeTransactionManagement",
                    "action" => "additionalFeeTransaction.deleted",
                    "authAdmin" => $authAdmin,
                    "data" => $allTransactionIds,
                    "message" => "Additional Fee Transaction Deleted",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => $studentIds,
                'feature'      => 'AdditionalFeeTransactionDelete',
                'message'      => 'Additional Fee Transaction Deleted',
                'data'         =>  $transaction,
            ]);
            return $successfulDeletions;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "A critical error occurred during the bulk transaction deletion process: " . $e->getMessage(),
                500,
                "Bulk Deletion Failed Due to System Error 🛑",
                "We were unable to complete the deletion due to an unexpected system issue. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkReverseTransaction($transactionIds, $currentSchool, $authAdmin)
    {

        if (empty($transactionIds)) {
            throw new AppException(
                "No transaction IDs were provided for bulk reversal.",
                400,
                "No IDs Provided 📝",
                "Please provide a list of transaction IDs you wish to reverse.",
                null
            );
        }

        $reversedTransactions = [];
        $allTransactionIds = collect($transactionIds)->pluck('transaction_id')->toArray();
        $notFoundIds = [];
        $missingFeeIds = [];
        $studentIds = [];

        DB::beginTransaction();

        try {
            foreach ($transactionIds as $item) {
                $transactionId = $item['transaction_id'];

                $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->find($transactionId);

                if (!$transaction) {
                    $notFoundIds[] = $transactionId;
                    continue;
                }

                $additionalFees = AdditionalFees::where('id', $transaction->additional_fee_id)
                    ->with(['specialty'])
                    ->where('school_branch_id', $currentSchool->id)
                    ->first();


                if (!$additionalFees) {
                    $missingFeeIds[] = $transactionId;
                    continue;
                }

                $additionalFees->status = 'unpaid';
                $additionalFees->save();

                $transaction->delete();

                $reversedTransactions[] = [
                    'transaction_id' => $transactionId,
                    'additional_fee_id' => $additionalFees->id,
                ];
                $studentIds[] =  $additionalFees->student_id;
            }


            if (!empty($notFoundIds) || !empty($missingFeeIds)) {
                DB::rollBack();

                $messages = [];
                if (!empty($notFoundIds)) {
                    $messages[] = "The following transaction IDs were not found: " . implode(', ', $notFoundIds) . ".";
                }
                if (!empty($missingFeeIds)) {
                    $messages[] = "The following transactions had their associated fee records missing (System Error): " . implode(', ', $missingFeeIds) . ".";
                }

                throw new AppException(
                    "Bulk reversal failed due to missing records. Details: " . implode(' | ', $messages),
                    404,
                    "Bulk Reversal Failed Due to Missing Records ⚠️",
                    "The entire operation was rolled back because some records required for the reversal were missing or invalid. Details: " . implode(' ', $messages),
                    null
                );
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.additionalFee.transactions.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "additionalFeeTransactionManagement",
                    "action" => "additionalFeeTransaction.reversed",
                    "authAdmin" => $authAdmin,
                    "data" => $allTransactionIds,
                    "message" => "Additional Fee Transaction Reversed",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => $studentIds,
                'feature'      => 'AdditionalFeeTransactionDelete',
                'message'      => 'Additional Fee Transaction Deleted',
                'data'         =>  $transaction,
            ]);
            event(new FinancialAnalyticsEvent(
                eventType: FinancialEventConstant::ADDITIONAL_FEE_REVERSED,
                version: 1,
                payload: [
                    "school_branch_id" => $currentSchool->id,
                    "category_id" => $additionalFees->additionalfee_category_id,
                    "amount" => $additionalFees->amount,
                    "specialty_id" => $additionalFees->specialty->id,
                    "department_id" => $additionalFees->specialty->department_id,
                    "level_id" => $additionalFees->specialty->level_id
                ]
            ));
            return $reversedTransactions;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "A critical error occurred during the bulk transaction reversal process: " . $e->getMessage(),
                500,
                "Bulk Reversal Failed Due to System Error 🛑",
                "We were unable to complete the reversal due to an unexpected system issue. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkPayAdditionalFee($feeDataList, $currentSchool, $authAdmin)
    {
        if (empty($feeDataList)) {
            throw new AppException(
                "The bulk payment list is empty.",
                400,
                "No Fees Provided 📝",
                "Please provide a list of additional fees to process for bulk payment.",
                null
            );
        }

        $transactionsToInsert = [];
        $notificationData = [];
        $failedPayments = [];
        $studentIds = [];
        $feeIds = collect($feeDataList)->pluck('fee_id')->toArray();

        $additionalFees = AdditionalFees::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $feeIds)
            ->with(['feeCategory', 'student', 'specialty'])
            ->get()
            ->keyBy('id');
        DB::beginTransaction();

        try {
            foreach ($feeDataList as $feeData) {
                $feeId = $feeData['fee_id'];
                $amountPaid = $feeData['amount'];
                $additionalFee = $additionalFees->get($feeId);

                if (!$additionalFee) {
                    $failedPayments[] = ['id' => $feeId, 'reason' => "Fee record not found for this school branch."];
                    continue;
                }

                if (bccomp(round($amountPaid, 2), round($additionalFee->amount, 2), 2) > 0) {
                    $failedPayments[] = ['id' => $feeId, 'reason' => "Amount paid (€{$amountPaid}) exceeds the amount owed (€{$additionalFee->amount})."];
                    continue;
                }

                if ($additionalFee->status === 'paid') {
                    $failedPayments[] = ['id' => $feeId, 'reason' => "Fee is already marked as paid."];
                    continue;
                }

                $transactionsToInsert[] = [
                    'id' => $transactionUuid = Str::uuid()->toString(),
                    'transaction_id' => 'ADF-' . substr(str_replace('-', '', $transactionUuid), 0, 10),
                    'amount' => $amountPaid,
                    'payment_method' => $feeData['payment_method'],
                    'fee_id' => $feeId,
                    'school_branch_id' => $currentSchool->id,
                    'additional_fee_id' => $feeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $notificationData[] = [
                    'student' => $additionalFee->student,
                    'student_name' => $additionalFee->student ? $additionalFee->student->name : 'N/A',
                    'amount_paid' => $amountPaid,
                    'fee_reason' => $additionalFee->reason,
                    'category_title' => $additionalFee->feeCategory->title ?? 'N/A',
                    'payment_method' => $feeData['payment_method'],
                    'fee_id' => $feeId,
                ];

                $studentIds[] = $additionalFee->student_id;
            }

            if (!empty($failedPayments)) {
                DB::rollBack();
                $failedList = collect($failedPayments)->map(fn($f) => "ID {$f['id']} ({$f['reason']})")->implode('; ');

                throw new AppException(
                    "Bulk payment failed for one or more fees. Failed IDs: " . implode(', ', collect($failedPayments)->pluck('id')->toArray()),
                    400,
                    "Bulk Payment Failed 🛑",
                    "The entire batch payment was stopped because of errors in the payment data for some fees. Details: {$failedList}",
                    null
                );
            }

            if (empty($transactionsToInsert)) {
                DB::rollBack();
                throw new AppException(
                    "No valid fees remain to be paid after all checks.",
                    400,
                    "No Valid Fees 📝",
                    "All fees provided were either already paid or had incorrect data.",
                    null
                );
            }

            DB::table('additional_fee_transactions')->insert($transactionsToInsert);

            $successfulFeeIds = collect($transactionsToInsert)->pluck('fee_id')->toArray();
            AdditionalFees::whereIn('id', $successfulFeeIds)->update(['status' => 'paid']);

            DB::commit();

            if (!empty($notificationData)) {
                AdminActionEvent::dispatch(
                    [
                        "permissions" =>  ["schoolAdmin.additionalFee.pay"],
                        "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                        "schoolBranch" =>  $currentSchool->id,
                        "feature" => "additionalFeeManagement",
                        "authAdmin" => $authAdmin,
                        "data" => $notificationData,
                        "message" => "Additional Fee Paid",
                    ]
                );
                StudentActionEvent::dispatch([
                    'schoolBranch' => $currentSchool->id,
                    'studentIds'   => $studentIds,
                    'feature'      => 'AdditionalFeeTransactionDelete',
                    'message'      => 'Additional Fee Transaction Deleted',
                    'data'         =>  $notificationData,
                ]);
                SendAdminAdditionalFeeNotificationJob::dispatch(
                    $currentSchool->id,
                    $notificationData,
                );

                SendAdditionalFeePaidNotificationJob::dispatch(
                    $notificationData
                );

                event(new FinancialAnalyticsEvent(
                    eventType: FinancialEventConstant::ADDITIONAL_FEE_PAID,
                    version: 1,
                    payload: [
                        "school_branch_id" => $currentSchool->id,
                        "category_id" => $additionalFee->additionalfee_category_id,
                        "amount" => $additionalFee->amount,
                        "specialty_id" => $additionalFee->specialty->id,
                        "department_id" => $additionalFee->specialty->department_id,
                        "level_id" => $additionalFee->specialty->level_id
                    ]
                ));
            }

            $newTransactionIds = collect($transactionsToInsert)->pluck('id')->toArray();
            return AdditionalFeeTransactions::whereIn('id', $newTransactionIds)->get();
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "A critical system error occurred during the bulk fee payment process: " . $e->getMessage(),
                500,
                "Payment Processing Failed 🚨",
                "We were unable to finalize the payment due to a critical system error. The operation has been rolled back. Please try again or contact support.",
                null
            );
        }
    }
}
