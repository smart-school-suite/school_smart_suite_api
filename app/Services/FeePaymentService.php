<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdminRegistrationFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendAdminTuitionFeePaidNotificationJob;
use App\Jobs\NotificationJobs\SendRegistrationFeePaidNotificationJob;
use App\Jobs\StatisticalJobs\FinancialJobs\RegistrationFeeStatJob;
use App\Jobs\StatisticalJobs\FinancialJobs\TuitionFeePaymentStatJob;
use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\RegistrationFeeTransactions;
use Illuminate\Support\Str;
use App\Models\Feepayment;
use App\Models\RegistrationFee;
use App\Models\TuitionFeeTransactions;
use App\Notifications\TuitionFeePaid;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FeePaymentService
{
    /**
     * Handles the payment of student tuition fees.
     *
     * @param array $data Contains payment details like 'tuition_id', 'amount', 'payment_method'.
     * @param object $currentSchool The current school branch object.
     * @return TuitionFees The updated TuitionFees model.
     * @throws Exception If any business rule or system error occurs.
     */
    public function payStudentFees(array $data, object $currentSchool): TuitionFees
    {
        DB::beginTransaction();

        try {
            $studentTuitionFees = TuitionFees::findOrFail($data['tuition_id']);

            $student = Student::where('school_branch_id', $currentSchool->id)
                ->where('id', $studentTuitionFees->student_id)
                ->first();

            if (!$student) {
                throw new Exception('Student not found for the given tuition record or school branch.', 404);
            }

            if ($data['amount'] <= 0) {
                throw new Exception('Payment amount must be greater than zero.', 400);
            }

            if ($data['amount'] > $studentTuitionFees->amount_left) {
                throw new Exception('The payment amount exceeds the remaining fee debt.', 409);
            }

            if ($student->payment_format === "one-time" && $studentTuitionFees->tution_fee_total != $data['amount']) {
                throw new Exception('Student payment structure requires a one-time payment for the full amount.', 400);
            }

            $studentTuitionFees->amount_paid += $data['amount'];
            $studentTuitionFees->amount_left -= $data['amount'];


            if ($studentTuitionFees->amount_left <= 0) {
                $studentTuitionFees->status = "completed";
                $studentTuitionFees->amount_left = 0;
            }

            $studentTuitionFees->save();

            $paymentId = Str::uuid();
            $transactionId = 'TXN-' . strtoupper(Str::random(10));

            TuitionFeeTransactions::create([
                'id' => $paymentId,
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'tuition_id' => $studentTuitionFees->id,
                'school_branch_id' => $currentSchool->id,
            ]);

            DB::commit();

            $paymentDetails = [
                'amountPaid' => $data['amount'],
                'balanceLeft' => $studentTuitionFees->amount_left,
                'paymentDate' => now()
            ];
            TuitionFeePaymentStatJob::dispatch($paymentId, $currentSchool->id);
            SendAdminTuitionFeePaidNotificationJob::dispatch($currentSchool->id, $student, $paymentDetails);
            $student->notify(new TuitionFeePaid($data['amount'], $studentTuitionFees->amount_left, now()));

            return $studentTuitionFees;
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error("Database error during student fee payment: " . $e->getMessage(), ['exception' => $e]);
            throw new Exception('A database error occurred during the payment process. Please try again.', 500);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function reverseFeePaymentTransaction(string $transactionId, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $transaction = TuitionFeeTransactions::where('school_branch_id', $currentSchool->id)
                ->with(['tuition'])
                ->find($transactionId);

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
    public function payRegistrationFees(array $data, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $registrationFeeData = [];
            $registrationFee = RegistrationFee::with(['student'])->where("school_branch_id", $currentSchool->id)
                ->find($data['registration_fee_id']);
            if (!$registrationFee) {
                return ApiResponseService::error("Student Registration Fee Appears To Be Deleted", null, 404);
            }
            if ($registrationFee->status === 'paid') {
                return ApiResponseService::error("Registration Fee Already Completed", null, 409);
            }

            if ($registrationFee->amount < $data['amount']) {
                return ApiResponseService::error("Amount Paid : {$data['amount']} is Greater than the registration fee: {$registrationFee->amount}.");
            }

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            $feePaymentId = Str::uuid();
            $transaction = RegistrationFeeTransactions::create([
                'id' => $feePaymentId,
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'registrationfee_id' => $data['registration_fee_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            $registrationFee->status = 'paid';
            $registrationFee->save();
            $registrationFeeData[] = [
                'student' => $registrationFee->student,
                'amount' => $data['amount'],
            ];
            DB::commit();
            RegistrationFeeStatJob::dispatch($feePaymentId, $currentSchool->id);
            SendAdminRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            SendRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            return $transaction;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function reverseRegistrationFeePaymentTransaction(string $transactionId, $currentSchool)
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
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            throw new Exception('Database error: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getFeesPaid($currentSchool)
    {
        $paidFeesData = Feepayment::where('school_branch_id', $currentSchool->id)
            ->with(['student.level'])
            ->with(['student.specialty'])
            ->get();

        if ($paidFeesData->isEmpty()) {
            return ApiResponseService::error('No records found', null, 400);
        }
        return $paidFeesData;
    }
    public function updateStudentFeesPayment(array $data, $feeId, $currentSchool)
    {
        $findFeePayment = Feepayment::where('school_branch_id', $currentSchool->id)
            ->find($feeId);
        if (!$findFeePayment) {
            return ApiResponseService::error('Fee Payment Not found', null, 400);
        }
        $filteredData = array_filter($data);
        $findFeePayment->update($filteredData);
        return $findFeePayment;
    }
    public function deleteTuitionFeeTransaction($transactionId, $currentSchool)
    {
        $transaction = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)->find($transactionId);
        if (!$transaction) {
            return ApiResponseService::error("Transaction Not found", null, 404);
        }
        $transaction->delete();
        return ApiResponseService::success("Tuition Transaction Deleted Successfully", $transaction, null, 200);
    }
    public function tuitionFeeTransactionDetails($transactionId, $currentSchool)
    {
        $transactionDetail = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['tuition', 'tuition.specialty', 'tuition.level', 'tuition.level', 'tuition.student'])
            ->find($transactionId);
        if (!$transactionDetail) {
            return ApiResponseService::error("Transaction Not Found", null, 400);
        }
        return $transactionDetail;
    }
    public function deleteFeePayment($feeId, $currentSchool)
    {
        $findFeePayment = Feepayment::where('school_branch_id', $currentSchool->id)
            ->find($feeId);
        if (!$findFeePayment) {
            return ApiResponseService::error('Fee Payment Not found', null, 400);
        }
        $findFeePayment->delete();
        return $findFeePayment;
    }
    public function getFeeDebtors($currentSchool)
    {
        $feeDebtors = Student::where('school_branch_id', $currentSchool->id)
            ->where('total_fee_debt', '>', 0)
            ->with(['specialty', 'level'])
            ->get();

        return $feeDebtors;
    }
    public function getTuitionFeeTransactions($currentSchool)
    {
        $getTuitionFeeTransactions = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)
        ->with(['tuition.student', 'tuition.specialty', 'tuition.level'])->get();
        return $getTuitionFeeTransactions;
    }
    public function getTuitionFees($currentSchool)
    {
        $tuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level'])->get();
        return $tuitionFees;
    }

    public function getTuitionFeeDetails($currentSchool, $feeId){
         $tuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)
                                   ->with(['student', 'specialty', 'level'])
                                  ->find($feeId);
        return $tuitionFees;

    }
    public function getRegistrationFees($currentSchool)
    {
        return RegistrationFee::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level'])->get();
    }
    public function getRegistrationFeeTransactions($currentSchool)
    {

        $transactions = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['registrationFee.student', 'registrationFee.level', 'registrationFee.specialty'])
            ->get();
        return $transactions;
    }
    public function getRegistrationFeeTransactionDetails($currentSchool, $transactionId){
        $transactionDetails = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
                             ->with(['registrationFee.student', 'registrationFee.level', 'registrationFee.specialty'])
                             ->find($transactionId);
        return $transactionDetails;
    }

    public function deleteRegistrationFeeTransaction($currentSchool, $transactionId){
        $transaction = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
                             ->findorFail($transactionId);
        $transaction->delete();
        return true;
    }
    public function bulkReverseRegistrationFeeTransaction($transactionIds, $currentSchool)
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
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkReverseTuitionFeeTransaction($transactionIds, $currentSchool)
    {
        $results = [];
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
            }
            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkPayRegistrationFee(array $feeDataArray, $currentSchool)
    {
        try {
            DB::beginTransaction();
            $registrationFeeData = [];
            foreach ($feeDataArray as $feeData) {
                $registrationFee = RegistrationFee::where("school_branch_id", $currentSchool->id)
                    ->with(['student'])
                    ->find($feeData['registration_fee_id']);
                if (!$registrationFee) {
                    return ApiResponseService::error("Student Registration Fee Appears To Be Deleted", null, 404);
                }
                if ($registrationFee->status === 'paid') {
                    return ApiResponseService::error("Registration Fee Already Completed", null, 409);
                }

                if ($registrationFee->amount < $feeData['amount']) {
                    return ApiResponseService::error("Amount Paid : {$feeData['amount']} is Greater than the registration fee: {$registrationFee->amount}.");
                }

                $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

                RegistrationFeeTransactions::create([
                    'transaction_id' => $transactionId,
                    'amount' => $feeData['amount'],
                    'payment_method' => $feeData['payment_method'],
                    'registrationfee_id' => $feeData['registration_fee_id'],
                    'school_branch_id' => $currentSchool->id,
                ]);

                $registrationFee->status = 'paid';
                $registrationFee->save();
                $registrationFeeData[] = [
                    'student' => $registrationFee->student,
                    'amount' => $feeData['amount'],
                ];
            }
            DB::commit();
            SendAdminRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            SendRegistrationFeePaidNotificationJob::dispatch(
                $registrationFeeData,
                $currentSchool->id
            );
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteTuitionFeeTransaction($transactionIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transactions = TuitionFeeTransactions::findOrFail($transactionId['transaction_id']);
                $transactions->delete();
                $result[] = $transactions;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function bulkDeleteRegistrationFeeTransactions($transactionIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($transactionIds as $transactionId) {
                $transaction = RegistrationFeeTransactions::findOrFail($transactionId['transaction_id']);
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
    public function bulkDeleteRegistrationFee($feeIds, $currentSchool)
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

 public function deleteRegistrationFee($feeId, $currentSchool)
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
}
