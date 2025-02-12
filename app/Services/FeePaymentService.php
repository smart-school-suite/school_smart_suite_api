<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Feepayment;

class FeePaymentService
{
    // Implement your logic here

    public function payStudentFees(array $data, $currentSchool)
    {
        $student = Student::where('school_branch_id', $currentSchool->id)->find($data["student_id"]);
        if (!$student) {
            return ApiResponseService::error('Student Not found', null, 404);
        }
        if ($student->total_fee_debt == 0) {
            return ApiResponseService::error('Student fees completed', null, 404);
        }
        if ($student->total_fee_debt < $data["amount"]) {
            return ApiResponseService::error("The amount being paid is greater than the student fee debt", null, 400);
        }

        $new_fee_payment_instance = new Feepayment();
        $new_fee_payment_instance->fee_name = $data["fee_name"];
        $new_fee_payment_instance->amount = $data["amount"];
        $new_fee_payment_instance->student_id = $data["student_id"];
        $new_fee_payment_instance->school_branch_id = $currentSchool->id;
        $new_fee_payment_instance->save();
        $student->total_fee_debt -= $data["amount"];

        if ($student->total_fee_debt == 0) {
            $student->fee_status = 'completed';
        }
        $student->save();
        return $student;
    }

    public function getFeesPaid($currentSchool)
    {
        $paidFeesData = Feepayment::where('school_branch_id', $currentSchool->id)
            ->with(['student.level'])
            ->with(['student.specialty'])
            ->get();

        if ($paidFeesData->isEmpty()) {
            return  ApiResponseService::error('No records found', null, 400);
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
}
