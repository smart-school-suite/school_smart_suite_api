<?php

namespace App\Services;

use App\Models\AdditionalFees;
use App\Models\AdditionalFeeTransactions;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class studentAdditionalFeeService
{
    // Implement your logic here

    public function createStudentAdditionalFees(array $additionalFees, $currentSchool)
    {
        $studentAdditionFees = new AdditionalFees();
        $studentAdditionFees->reason = $additionalFees['reason'];
        $studentAdditionFees->amount = $additionalFees['amount'];
        $studentAdditionFees->additional_fee_category = $additionalFees['additional_fee_category'];
        $studentAdditionFees->school_branch_id = $currentSchool->id;
        $studentAdditionFees->specialty_id = $additionalFees['specialty_id'];
        $studentAdditionFees->level_id = $additionalFees['level_id'];
        $studentAdditionFees->student_id = $additionalFees['student_id'];
        $studentAdditionFees->save();
        return $studentAdditionFees;
    }

    public function deleteStudentAdditionalFees(string $feeId, $currentSchool)
    {
        $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        Log::info($feeId);
        if (!$studentAdditionFeesExist) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }
        $studentAdditionFeesExist->delete();
        return $studentAdditionFeesExist;
    }

    public function updateStudentAdditionalFees(array $additionalFeesData, string $feeId, $currentSchool)
    {
        $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        if (!$studentAdditionFeesExist) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }

        $removedEmptyInputs = array_filter($additionalFeesData);
        $studentAdditionFeesExist->update($removedEmptyInputs);
        return $studentAdditionFeesExist;
    }

    public function getStudentAdditionalFees(string $studentId, $currentSchool)
    {
        $studentAdditionFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->where("student_id", $studentId)->with(['student', 'specialty', 'level', 'feeCategory'])->get();
        return $studentAdditionFees;
    }

    public function getAdditionalFees($currentSchool)
    {
        $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level', 'feeCategory'])->get();
        return $additionalFees;
    }

    public function payAdditionalFees($additionalFeesData, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($additionalFeesData['fee_id']);
            if (!$studentAdditionFeesExist) {
                return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
            }

            if (round($studentAdditionFeesExist->amount, 2) < round($additionalFeesData['amount'], 2)) {
                return ApiResponseService::error("Amount Paid Exceeds The Amount Owed", null, 400);
            }

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            $transaction = AdditionalFeeTransactions::create([
                'transaction_id' => $transactionId,
                'amount' => $additionalFeesData['amount'],
                'payment_method' => $additionalFeesData['payment_method'],
                'fee_id' => $additionalFeesData['fee_id'],
                'school_branch_id' => $currentSchool->id,
                'additional_fee_id' => $additionalFeesData['fee_id'],
            ]);
            $studentAdditionFeesExist->status =  'paid';
            $studentAdditionFeesExist->save();
            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function reverseTransaction($transactionId, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->find($transactionId);

            if (!$transaction) {
                return ApiResponseService::error("Transaction Not Found", null, 404);
            }

            $additionalFees = AdditionalFees::where('id', $transaction->additional_fee_id)
                ->where('school_branch_id', $currentSchool->id)
                ->first();

            if (!$additionalFees) {
                return ApiResponseService::error("Associated Additional Fees Not Found", null, 404);
            }
            $additionalFees->status = 'unpaid';
            $additionalFees->save();

            $transaction->delete();

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseService::error("An error occurred while reversing the transaction: " . $e->getMessage(), null, 500);
        }
    }
    public function getAdditionalFeesTransactions($currentSchool)
    {
        $getAdditionalFeesTransactions = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)->with(['additionFee.feeCategory', 'additionFee.student'])->get();
        return $getAdditionalFeesTransactions;
    }
    public function deleteTransaction($transactionId, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->findOrFail($transactionId);

            if (!$transaction) {
                return ApiResponseService::error("Transaction Not Found", null, 404);
            }

            $transaction->delete();

            DB::commit();
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            return ApiResponseService::error("An error occurred while deleting the transaction: " . $e->getMessage(), null, 500);
        }
    }

    public function getTransactionDetail($transationId, $currentSchool){
        $transactionDetials = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)
                                ->with(['additionFee', 'additionFee.feeCategory', 'additionFee.student', 'additionFee.specialty', 'additionFee.level'])
                                ->findOrFail($transationId);
        return $transactionDetials;
    }
}
