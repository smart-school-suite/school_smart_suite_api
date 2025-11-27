<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\TuitionFee\PayTuitionFeeRequest;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeePaymentService;
use App\Services\ApiResponseService;

class TuitionFeePaymentController extends Controller
{
    protected TuitionFeePaymentService $tuitionFeePaymentService;

    public function __construct(TuitionFeePaymentService $tuitionFeePaymentService)
    {
        $this->tuitionFeePaymentService = $tuitionFeePaymentService;
    }

    public function payTuitionFees(PayTuitionFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payFees = $this->tuitionFeePaymentService->payStudentFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Fees Paid Sucessfully", $payFees, null, 201);
    }
    public function getStudentFinancialTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authStudent = $this->resolveUser();
        $transactions = $this->tuitionFeePaymentService->getStudentFinancialTransactions($currentSchool, $authStudent);
        return ApiResponseService::success("Student Transactions Fetched Successfully", $transactions, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
