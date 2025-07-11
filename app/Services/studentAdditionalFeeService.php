<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdditionalFeeNotification;
use App\Jobs\NotificationJobs\SendAdditionalFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendAdminAdditionalFeeNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeStatJob;
use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeTransactionJob;
use App\Models\AdditionalFees;
use App\Models\AdditionalFeeTransactions;
use App\Models\Country;
use App\Models\Student;
use App\Notifications\AdditionalFee;
use App\Notifications\AdditionalFeePaidNotification;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentAdditionalFeeService
{
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
        $student->notify(new AdditionalFee($additionalFees['amount'], $additionalFees['reason']));
        return $studentAdditionFees;
    }
    public function deleteStudentAdditionalFees(string $feeId, $currentSchool)
    {
        $additionalFee = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        if (!$additionalFee) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }
        $additionalFee->delete();
        return $additionalFee;
    }
    public function updateStudentAdditionalFees(array $additionalFeesData, string $feeId, $currentSchool)
    {
        $additionalFee = AdditionalFees::where("school_branch_id", $currentSchool->id)->find($feeId);
        if (!$additionalFee) {
            return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
        }

        $removedEmptyInputs = array_filter($additionalFeesData);
        $additionalFee->update($removedEmptyInputs);
        return $additionalFee;
    }
    public function getStudentAdditionalFees(string $studentId, $currentSchool)
    {
        $studentAdditionFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->where("student_id", $studentId)->with(['student', 'specialty', 'level', 'feeCategory'])->get();
        return $studentAdditionFees;
    }
    public function getAdditionalFeeDetails($currentSchool, string $feeId){
       return  AdditionalFees::where("school_branch_id", $currentSchool->id)
                               ->where("id", $feeId)
                               ->with(['student', 'specialty', 'level', 'feeCategory'])->get();
    }
    public function getAdditionalFees($currentSchool)
    {
        $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level', 'feeCategory'])->get();
        return $additionalFees;
    }
    public function payAdditionalFees(array $additionalFeesData,  $currentSchool): AdditionalFeeTransactions
    {
        DB::beginTransaction();

        try {
            $additionalFee = AdditionalFees::where('school_branch_id', $currentSchool->id)
                ->with(['feeCategory', 'student'])
                ->find($additionalFeesData['fee_id']);

            if (!$additionalFee) {
                throw new Exception("Student additional fee record not found.", 404);
            }

            if (bccomp($additionalFeesData['amount'], $additionalFee->amount, 2) > 0) {
                throw new Exception("Amount paid exceeds the amount owed for this additional fee.", 400);
            }

            $transactionId = Str::random(10);
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

            $additionalFee->status = 'paid';
            $additionalFee->save();

            DB::commit();

            AdditionalFeeTransactionJob::dispatch($feeTransactionId, $currentSchool->id);

            if ($additionalFee->student) {
                $additionalFee->student->notify(new AdditionalFeePaidNotification(
                    $additionalFeesData['amount'],
                    $additionalFee->reason,
                    $additionalFee->feeCategory->title,
                    'XAF'
                ));
            }


            $adminNotificationData = [
                'student_name' => $additionalFee->student ? $additionalFee->student->name : 'N/A',
                'amount_paid' => $additionalFeesData['amount'],
                'fee_reason' => $additionalFee->reason,
                'fee_category_title' => $additionalFee->feeCategory->title,
                'payment_method' => $additionalFeesData['payment_method'],
            ];

            SendAdminAdditionalFeeNotificationJob::dispatch(
                $adminNotificationData,
                $currentSchool->id
            );

            return $transaction;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("An unexpected error occurred while processing the additional fee payment. Please try again later.", 500, $e);
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
    public function getTransactionDetail($transationId, $currentSchool)
    {
        $transactionDetials = AdditionalFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['additionFee', 'additionFee.feeCategory', 'additionFee.student', 'additionFee.specialty', 'additionFee.level'])
            ->findOrFail($transationId);
        return $transactionDetials;
    }
    public function bulkBillStudents(array $studentList, $currentSchool)
    {
        $result = [];
        $additionalFeeData = [];
        try {
            DB::beginTransaction();
            foreach ($studentList as $student) {
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
                $additionalFeeData[] = [
                    'student' => Student::find('student_id'),
                    'amount' => $student['amount'],
                    'reason' => $student['reason']
                ];
            }
            DB::commit();
            SendAdditionalFeeNotification::dispatch($additionalFeeData);
            SendAdminAdditionalFeeNotificationJob::dispatch($currentSchool->id, $additionalFeeData);
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteStudentAdditionalFees($additionalFeeIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($additionalFeeIds as $additionalFeeId) {
                $studentAdditionalFee = AdditionalFees::findOrFail($additionalFeeId['fee_id']);
                $studentAdditionalFee->delete();
                $result[] = [
                    $studentAdditionalFee
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteTransaction($transactionIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = AdditionalFeeTransactions::findOrFail($transactionId['transaction_id']);
                $transaction->delete();
                $result[] = [
                    $transaction
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReverseTransaction($transactionIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
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
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkPayAdditionalFee($feeDataList, $currentSchool)
    {
        $result = [];
        try {
            $notificationData = [];
            DB::beginTransaction();
            foreach ($feeDataList as $feeData) {
                $additionalFee = AdditionalFees::where("school_branch_id", $currentSchool->id)
                 ->with(['feeCategory', 'student'])
                 ->find($feeData['fee_id']);
                if (!$additionalFee) {
                    return ApiResponseService::error("Student Additional Fees Appears To Be Deleted", null, 404);
                }

                if (round($additionalFee->amount, 2) < round($feeData['amount'], 2)) {
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
                $additionalFee->status =  'paid';
                $additionalFee->save();
                  $$notificationData[] = [
                'student' => $additionalFee->student,
                'student_name' => $additionalFee->student ? $additionalFee->student->name : 'N/A',
                'amount_paid' => $feeData['amount'],
                'fee_reason' => $additionalFee->reason,
                'category_title' => $additionalFee->feeCategory->title,
                'payment_method' => $feeData['payment_method'],
            ];
                $result[] = [
                    $transaction,
                    $additionalFee
                ];
            }
            DB::commit();
            SendAdminAdditionalFeeNotificationJob::dispatch(
                $notificationData,
                $currentSchool->id
            );
            SendAdditionalFeePaidNotificationJob::dispatch(
                $notificationData
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
