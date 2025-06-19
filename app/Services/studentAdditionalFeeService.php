<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeStatJob;
use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeTransactionJob;
use App\Models\AdditionalFees;
use App\Models\AdditionalFeeTransactions;
use App\Models\Student;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentAdditionalFeeService
{
    // Implement your logic here

    public function createStudentAdditionalFees(array $additionalFees, $currentSchool)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)->find($additionalFees['student_id']);
        $studentAdditionFees = new AdditionalFees();
        $additionalFeeId = Str::uuid();
        $studentAdditionFees->id = $additionalFeeId;
        $studentAdditionFees->reason = $additionalFees['reason'];
        $studentAdditionFees->amount = $additionalFees['amount'];
        $studentAdditionFees->additionalfee_category_id = $additionalFees['additionalfee_category_id'];
        $studentAdditionFees->school_branch_id = $currentSchool->id;
        $studentAdditionFees->specialty_id = $student->specialty_id;
        $studentAdditionFees->level_id = $student->level_id;
        $studentAdditionFees->student_id = $student->id;
        $studentAdditionFees->save();
        AdditionalFeeStatJob::dispatch($additionalFeeId, $currentSchool->id);
        return $studentAdditionFees;
    }

    public function deleteStudentAdditionalFees(string $feeId, $currentSchool)
    {
        $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
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
            $feeTransactionId = Str::uuid();
            $transaction = AdditionalFeeTransactions::create([
                'id' => $feeTransactionId,
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
            AdditionalFeeTransactionJob::dispatch($feeTransactionId, $currentSchool->id);
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
    public function bulkBillStudents(array $studentList, $currentSchool){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($studentList as $student){
                $studentAdditionFees = new AdditionalFees();
                $studentAdditionFees->reason = $student['reason'];
                $studentAdditionFees->amount = $student['amount'];
                $studentAdditionFees->additional_fee_category = $student['additional_fee_category'];
                $studentAdditionFees->school_branch_id = $currentSchool->id;
                $studentAdditionFees->specialty_id = $student['specialty_id'];
                $studentAdditionFees->level_id = $student['level_id'];
                $studentAdditionFees->student_id = $student['student_id'];
                $studentAdditionFees->save();
                $result[] = [
                     $studentAdditionFees
                ];
            }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteStudentAdditionalFees($additionalFeeIds){
         $result = [];
         try{
            DB::beginTransaction();
            foreach($additionalFeeIds as $additionalFeeId){
              $studentAdditionalFee = AdditionalFees::findOrFail($additionalFeeId['fee_id']);
              $studentAdditionalFee->delete();
              $result[] = [
                 $studentAdditionalFee
              ];
            }
            DB::commit();
           return $result;
         }
         catch(Exception $e){
            DB::rollBack();
            throw $e;
         }
    }

    public function bulkDeleteTransaction($transactionIds){
         $result = [];
         try{
             DB::beginTransaction();
             foreach($transactionIds as $transactionId){
                 $transaction = AdditionalFeeTransactions::findOrFail($transactionId['transaction_id']);
                 $transaction->delete();
                 $result[] = [
                     $transaction
                 ];
             }
             DB::commit();
             return $result;
         }
         catch(Exception $e){
            DB::rollBack();
            throw $e;
         }
    }

    public function bulkReverseTransaction($transactionIds, $currentSchool){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($transactionIds as $transactionId){
                $transaction = AdditionalFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->find($transactionId['transaction_id']);

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

            $result[] = [
                 $additionalFees,
                 $transaction,
            ];
            }
            DB::commit();
            return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkPayAdditionalFee($feeDataList, $currentSchool){
        $result = [];
        try{
            DB::beginTransaction();
           foreach($feeDataList as $feeData){
            $studentAdditionFeesExist = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeData['fee_id']);
            if (!$studentAdditionFeesExist) {
                return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
            }

            if (round($studentAdditionFeesExist->amount, 2) < round($feeData['amount'], 2)) {
                return ApiResponseService::error("Amount Paid Exceeds The Amount Owed", null, 400);
            }

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            $transaction = AdditionalFeeTransactions::create([
                'transaction_id' => $transactionId,
                'amount' => $feeData['amount'],
                'payment_method' => $feeData['payment_method'],
                'fee_id' => $feeData['fee_id'],
                'school_branch_id' => $currentSchool->id,
                'additional_fee_id' => $feeData['fee_id'],
            ]);
            $studentAdditionFeesExist->status =  'paid';
            $studentAdditionFeesExist->save();

            $result[] = [
                 $transaction,
                 $studentAdditionFeesExist
            ];
           }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
