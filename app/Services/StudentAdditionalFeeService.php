<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdditionalFeeNotification;
use App\Jobs\NotificationJobs\SendAdditionalFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendAdminAdditionalFeeNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeStatJob;
use App\Jobs\StatisticalJobs\FinancialJobs\AdditionalFeeTransactionJob;
use App\Models\AdditionalFees;
use App\Models\AdditionalFeeTransactions;
use App\Models\Student;
use App\Notifications\AdditionalFee;
use App\Notifications\AdditionalFeePaidNotification;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Exceptions\AppException;

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
        $studentAdditionFees->status = "unpaid";
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
        try {
            $studentAdditionFees = AdditionalFees::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $studentId)
                ->with(['student', 'specialty', 'level', 'feeCategory'])
                ->get();

            if ($studentAdditionFees->isEmpty()) {
                throw new AppException(
                    "No additional fees were found for this student.",
                    404,
                    "No Additional Fees Found",
                    "There are no additional fee records available for the specified student in the system.",
                    null
                );
            }

            return $studentAdditionFees;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving student additional fees.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the additional fee data from being retrieved successfully.",
                null
            );
        }
    }
    public function getAdditionalFeeDetails($currentSchool, string $feeId)
    {
        return  AdditionalFees::where("school_branch_id", $currentSchool->id)
            ->where("id", $feeId)
            ->with(['student', 'specialty', 'level', 'feeCategory'])->get();
    }
    public function getAdditionalFees($currentSchool)
    {
        try {
            $additionalFees = AdditionalFees::where("school_branch_id", $currentSchool->id)
                ->with(['student', 'specialty', 'level', 'feeCategory'])
                ->get();

            if ($additionalFees->isEmpty()) {
                throw new AppException(
                    "No additional fees were found for this school branch.",
                    404,
                    "No Additional Fees Found",
                    "There are no additional fee records available in the system for your school branch.",
                    null
                );
            }

            return $additionalFees;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving additional fees.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of additional fees from being retrieved successfully.",
                null
            );
        }
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
                'student' => $additionalFee->student ?? null,
                'amount_paid' => $additionalFeesData['amount'],
                'fee_reason' => $additionalFee->reason,
                'fee_category_title' => $additionalFee->feeCategory->title,
                'payment_method' => $additionalFeesData['payment_method'],
            ];

            SendAdminAdditionalFeeNotificationJob::dispatch(
                $currentSchool->id,
                $adminNotificationData
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

            $additionalFees = AdditionalFees::where('id', $transaction->fee_id)
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
            DB::rollBack();
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
        try {
            DB::beginTransaction();
            $studentsToInsert = [];
            $additionalFeeData = [];

            $studentIds = collect($studentList)->pluck('student_id')->toArray();
            $students = Student::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $studentIds)
                ->get()
                ->keyBy('id');

            foreach ($studentList as $studentData) {
                $student = $students->get($studentData['student_id']);

                if ($student) {
                    $studentsToInsert[] = [
                        'id' => Str::uuid(),
                        'reason' => $studentData['reason'],
                        'amount' => $studentData['amount'],
                        'status' => 'unpaid',
                        'additionalfee_category_id' => $studentData['additionalfee_category_id'],
                        'school_branch_id' => $currentSchool->id,
                        'specialty_id' => $student->specialty_id,
                        'level_id' => $student->level_id,
                        'student_id' => $student->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $additionalFeeData[] = [
                        'student' => $student,
                        'amount' => $studentData['amount'],
                        'reason' => $studentData['reason']
                    ];
                }
            }

            if (!empty($studentsToInsert)) {
                DB::table('additional_fees')->insert($studentsToInsert);
            }

            DB::commit();

            if (!empty($additionalFeeData)) {
                SendAdditionalFeeNotification::dispatch($additionalFeeData);
                SendAdminAdditionalFeeNotificationJob::dispatch($currentSchool->id, $additionalFeeData);
            }

            return $studentsToInsert;
        } catch (Throwable $e) {
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
        try {
            DB::beginTransaction();
            $feeIds = collect($feeDataList)->pluck('fee_id')->toArray();

            $additionalFees = AdditionalFees::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $feeIds)
                ->with(['feeCategory', 'student'])
                ->get()
                ->keyBy('id');

            $transactionsToInsert = [];
            $notificationData = [];

            foreach ($feeDataList as $feeData) {
                $additionalFee = $additionalFees->get($feeData['fee_id']);

                if (!$additionalFee) {
                    throw new Exception("Student Additional Fees Appears To Be Deleted", 404);
                }

                if (round($additionalFee->amount, 2) < round($feeData['amount'], 2)) {
                    throw new Exception("Amount Paid Exceeds The Amount Owed", 400);
                }

                $transactionsToInsert[] = [
                    'id' => Str::uuid(),
                    'transaction_id' => 'ADF-' . substr(str_replace('-', '', Str::uuid()->toString()), 0, 10),
                    'amount' => $feeData['amount'],
                    'payment_method' => $feeData['payment_method'],
                    'fee_id' => $feeData['fee_id'],
                    'school_branch_id' => $currentSchool->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $notificationData[] = [
                    'student' => $additionalFee->student,
                    'student_name' => $additionalFee->student ? $additionalFee->student->name : 'N/A',
                    'amount_paid' => $feeData['amount'],
                    'fee_reason' => $additionalFee->reason,
                    'category_title' => $additionalFee->feeCategory->title,
                    'payment_method' => $feeData['payment_method'],
                ];
            }

            DB::table('additional_fee_transactions')->insert($transactionsToInsert);

            AdditionalFees::whereIn('id', $feeIds)->update(['status' => 'paid']);

            DB::commit();

            if (!empty($notificationData)) {
                SendAdminAdditionalFeeNotificationJob::dispatch(
                    $currentSchool->id,
                    $notificationData,
                );

                SendAdditionalFeePaidNotificationJob::dispatch(
                    $notificationData
                );
            }

            return collect($transactionsToInsert)->map(function ($item) {
                return AdditionalFeeTransactions::find($item['id']);
            });
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkUpdateStudentAdditionalFees($additionalFees, $currentSchool)
    {
        try {
            DB::beginTransaction();

            $updates = collect($additionalFees)
                ->filter(fn($fee) => isset($fee['fee_id']) && $fee['fee_id'] !== null)
                ->map(function ($fee) {
                    return collect($fee)->filter(fn($value) => !is_null($value));
                });

            $feeIds = $updates->pluck('fee_id');

            $existingFees = AdditionalFees::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $feeIds)
                ->get()
                ->keyBy('id');

            $updates->each(function ($data) use ($existingFees) {
                $feeId = $data['fee_id'];
                if ($existingFees->has($feeId)) {
                    $fee = $existingFees->get($feeId);
                    $fee->update($data->except('fee_id')->toArray());
                }
            });

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
