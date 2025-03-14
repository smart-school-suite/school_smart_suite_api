<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\RegistrationFeeTransactions;
use Illuminate\Support\Str;
use App\Models\Feepayment;
use App\Models\RegistrationFee;
use App\Models\TuitionFeeTransactions;
use Exception;

class FeePaymentService
{
    public function payStudentFees(array $data, $currentSchool)
    {
        DB::beginTransaction();

        try {
            $studentTuitionFees = TuitionFees::find($data['tuition_id']);
            $student = Student::where('school_branch_id', $currentSchool->id)->find($studentTuitionFees->student_id);
            if (!$student) {
                throw new Exception('Student not found', 404);
            }
            if (!$studentTuitionFees) {
                throw new Exception("Tuition fees record not found", 404);
            }
            if ($studentTuitionFees->tution_fee_total != $data['amount'] && $student->payment_format === "one time") {
                throw new Exception('Student Payment Stucture requires A one time Payment');
            }

            if ($data['amount'] > $studentTuitionFees->amount_left) {
                throw new Exception("The fee debt is less than the amount paid", 409);
            }

            $studentTuitionFees->amount_paid += $data['amount'];
            $studentTuitionFees->amount_left -= $data['amount'];

            if ($studentTuitionFees->amount_left === 0) {
                $studentTuitionFees->status = "completed";
            }
            $studentTuitionFees->save();

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            TuitionFeeTransactions::create([
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'tuition_id' => $data['tuition_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            DB::commit();
            return $studentTuitionFees;
        } catch (QueryException $e) {
            DB::rollBack();
            throw new Exception('Database error: ' . $e->getMessage(), 500);
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
            $studentRegistrationExists = RegistrationFee::where("school_branch_id", $currentSchool->id)->find($data['registration_fee_id']);
            if (!$studentRegistrationExists) {
                return ApiResponseService::error("Student Registration Fee Appears To Be Deleted", null, 404);
            }
            if ($studentRegistrationExists->status === 'paid') {
                return ApiResponseService::error("Registration Fee Already Completed", null, 409);
            }

            if ($studentRegistrationExists->amount < $data['amount']) {
                return ApiResponseService::error("Amount Paid : {$data['amount']} is Greater than the registration fee: {$studentRegistrationExists->amount}.");
            }

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);

            $transaction = RegistrationFeeTransactions::create([
                'transaction_id' => $transactionId,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'registrationfee_id' => $data['registration_fee_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            $studentRegistrationExists->status = 'paid';
            $studentRegistrationExists->save();
            DB::commit();
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
            $registrationFees->status = 'unpaid';
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

    public function updateStudentFeesPayment(array $data, $fee_id, $currentSchool)
    {
        $findFeePayment = Feepayment::where('school_branch_id', $currentSchool->id)
            ->find($fee_id);
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
            ->with(['tuition', 'tuition.specialty', 'tuition.level', 'tuition.level'])
            ->find($transactionId);
        if (!$transactionDetail) {
            return ApiResponseService::error("Transaction Not Found", null, 400);
        }
        return $transactionDetail;
    }
    public function deleteFeePayment($fee_id, $currentSchool)
    {
        $findFeePayment = Feepayment::where('school_branch_id', $currentSchool->id)
            ->find($fee_id);
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
        $getTuitionFeeTransactions = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)->with(['tuition', 'tuition.student', 'tuition.specialty', 'tuition.level'])->get();
        return $getTuitionFeeTransactions;
    }

    public function getTuitionFees($currentSchool)
    {
        $tuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level'])->get();
        return $tuitionFees;
    }

    public function getRegistrationFees($currentSchool)
    {
        return RegistrationFee::where("school_branch_id", $currentSchool->id)->with(['student', 'specialty', 'level'])->get();
    }

    public function getRegistrationFeeTransactions($currentSchool)
    {

        $transactions = RegistrationFeeTransactions::where("school_branch_id", $currentSchool->id)
            ->with(['registrationFee', 'registrationFee.student', 'registrationFee.level', 'registrationFee.specialty'])
            ->get();
        return $transactions;
    }
}
