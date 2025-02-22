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
            $student = Student::where('school_branch_id', $currentSchool->id)->find($data["student_id"]);
            if (!$student) {
                throw new Exception('Student not found', 404);
            }

            $new_fee_payment_instance = new Feepayment();
            $new_fee_payment_instance->fee_name = $data["fee_name"];
            $new_fee_payment_instance->amount = $data["amount"];
            $new_fee_payment_instance->student_id = $data["student_id"];
            $new_fee_payment_instance->school_branch_id = $currentSchool->id;
            $new_fee_payment_instance->save();
            $studentTuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $data["student_id"])
                ->where("level_id", $data['level_id'])
                ->where("specialty_id", $data['specialty_id'])
                ->first();
            if (!$studentTuitionFees) {
                throw new Exception("Tuition fees record not found", 404);
            }
            if ($data['tution_fee_total'] != $data['amount'] && $student->payment_format === "one time") {
                throw new Exception('Student Payment Stucture requires A one time Payment');
            }

            if ($studentTuitionFees->amount_left < $data['amount']) {
                throw new Exception("The fee debt is less than the amount paid", 409);
            }

            $studentTuitionFees->amount_paid += $data['amount'];
            $studentTuitionFees->amount_left -= $data['amount'];

            if ($studentTuitionFees->amount_left === 0) {
                $studentTuitionFees->status = "completed";
            }
            $studentTuitionFees->save();

            $transactionId = substr(str_replace('-', '', Str::uuid()->toString()), 0, 10);
            TuitionFeeTransactions::created([
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

    public function reverseStudentFeesPayment(string $paymentId, $currentSchool)
    {
        DB::beginTransaction();
        try {
            $feePayment = Feepayment::where('id', $paymentId)
                ->where('school_branch_id', $currentSchool->id)
                ->first();

            if (!$feePayment) {
                throw new Exception("Payment record not found", 404);
            }
            $studentTuitionFees = TuitionFees::where("school_branch_id", $currentSchool->id)
                ->where("student_id", $feePayment->student_id)
                ->where("level_id", $feePayment->level_id)
                ->where("specialty_id", $feePayment->specialty_id)
                ->first();

            if (!$studentTuitionFees) {
                throw new Exception("Tuition fees record not found", 404);
            }
            $studentTuitionFees->amount_paid -= $feePayment->amount;
            $studentTuitionFees->amount_left += $feePayment->amount;
            if ($studentTuitionFees->amount_left > 0) {
                $studentTuitionFees->status = "pending";
            } else {
                $studentTuitionFees->status = "completed";
            }

            $studentTuitionFees->save();
            $feePayment->delete();

            DB::commit();

            return ApiResponseService::success('Payment successfully reversed', null);
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
            if ($studentRegistrationExists->status = 'completed') {
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
                'registration_fee_id' => $data['registration_fee_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            $studentRegistrationExists->status = 'completed';
            $studentRegistrationExists->save();
            DB::commit();
            return $transaction;
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
        $getTuitionFeeTransactions = TuitionFeeTransactions::where("school_branch_id", $currentSchool->id)->with(['tuition', 'tuition.student', 'tuition.specialty'])->get();
        return $getTuitionFeeTransactions;
    }
}
