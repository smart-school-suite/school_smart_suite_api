<?php

namespace App\Services\Resit;

use App\Jobs\StatisticalJobs\FinancialJobs\ResitFeeStatJob;
use App\Models\ResitFeeTransactions;
use App\Services\ApiResponseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResitPayment;
use Exception;
use App\Models\Studentresit;
use App\Exceptions\AppException;

class ResitPaymentService
{
    public function payResit($studentResitData, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
                ->with(['courses', 'student'])
                ->find($studentResitData['student_resit_id']);

            if (!$studentResit) {
                return ApiResponseService::error("Student Resit Not found", null, 404);
            }

            if ($studentResit->resit_fee < $studentResitData['amount']) {
                return ApiResponseService::error("The Amount paid is greater than the cost of resit", null, 409);
            }
            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

            $transactionId = Str::uuid();
            ResitFeeTransactions::create([
                'id' => $transactionId,
                'amount' => $studentResitData['amount'],
                'payment_method' => $studentResitData['payment_method'],
                'resitfee_id' => $studentResitData['student_resit_id'],
                'school_branch_id' => $currentSchool->id,
                'transaction_id' => $transactionId
            ]);

            $studentResit->paid_status = "Paid";
            $studentResit->save();
            DB::commit();
            ResitFeeStatJob::dispatch($transactionId, $currentSchool->id);
            $paymentDetails = [
                'amount' => $studentResit->resit_fee,
                'transactionRef' => $transactionId,
                'courseName' => $studentResit->courses->course_title
            ];
            $studentResit->student->notify(new ResitPayment($paymentDetails));
            return $studentResit;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getResitPaymentTransactions($currentSchool)
    {
        try {
            $getResitPaymentTransactions = ResitFeeTransactions::where("school_branch_id", $currentSchool->id)
                ->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level', 'studentResit.courses'])
                ->get();

            if ($getResitPaymentTransactions->isEmpty()) {
                throw new AppException(
                    "No resit payment transactions were found for this school branch.",
                    404,
                    "No Transactions Found",
                    "There are currently no resit fee transaction records available in the system for your school branch.",
                    null
                );
            }

            return $getResitPaymentTransactions;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving resit payment transactions.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of transactions from being retrieved successfully.",
                null
            );
        }
    }
    public function deleteResitFeeTransaction($currentSchool, string $transactionId)
    {
        $resitTransaction = ResitFeeTransactions::where("school_branch_id", $currentSchool->id)->find($transactionId);
        if (!$resitTransaction) {
            return ApiResponseService::error("Resit Transaction Not Found", null, 200);
        }
        $resitTransaction->delete();
        return $resitTransaction;
    }
    public function getTransactionDetails($currentSchool, string $transactionId)
    {
        return ResitFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['studentResit', 'studentResit.student', 'studentResit.specialty', 'studentResit.level', 'studentResit.courses'])
            ->find($transactionId);
    }
    public function reverseResitTransaction($transactionId, $currentSchool)
    {
        DB::beginTransaction();
        try {

            $transaction = ResitFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->find($transactionId);

            if (!$transaction) {
                return ApiResponseService::error("Transaction Not found", null, 404);
            }

            $studentResit = Studentresit::where('school_branch_id', $currentSchool->id)
                ->find($transaction->resitfee_id);

            if (!$studentResit) {
                return ApiResponseService::error("Student Resit Not found", null, 404);
            }

            if ($studentResit->paid_status !== "Paid") {
                return ApiResponseService::error("The resit fee is not currently marked as paid", null, 409);
            }

            $transaction->delete();

            $studentResit->paid_status = "unpaid";
            $studentResit->save();

            DB::commit();

            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkPayStudentResit($studentResitIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($studentResitIds as $studentResitId) {
                $studentResit = Studentresit::where("school_branch_id", $currentSchool->id)
                    ->find($studentResitId['resit_id']);

                if (!$studentResit) {
                    return ApiResponseService::error("Student Resit Not found", null, 404);
                }
                $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

                ResitFeeTransactions::create([
                    'amount' => $studentResitId['amount'],
                    'payment_method' => $studentResitId['payment_method'],
                    'resitfee_id' => $studentResitId['resit_id'],
                    'school_branch_id' => $currentSchool->id,
                    'transaction_id' => $transactionId
                ]);

                $studentResit->paid_status = "Paid";
                $studentResit->save();
                $result[] = [
                    $studentResit
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
                $transaction = ResitFeeTransactions::findOrFail($transactionId['transaction_id']);
                $transaction->delete();
                $result[] = $transaction;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReverseResitTransaction($transactionIds, $currentSchool)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = ResitFeeTransactions::where('school_branch_id', $currentSchool->id)
                    ->find($transactionId['transaction_id']);

                if (!$transaction) {
                    return ApiResponseService::error("Transaction Not found", null, 404);
                }

                $studentResit = Studentresit::where('school_branch_id', $currentSchool->id)
                    ->find($transaction->resitfee_id);

                if (!$studentResit) {
                    return ApiResponseService::error("Student Resit Not found", null, 404);
                }

                if ($studentResit->paid_status !== "Paid") {
                    return ApiResponseService::error("The resit fee is not currently marked as paid", null, 409);
                }

                $transaction->delete();

                $studentResit->paid_status = "unpaid";
                $studentResit->save();
                $result[] = [
                    $studentResit
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
