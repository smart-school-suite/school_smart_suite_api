<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\FeePaymentService;
use App\Http\Requests\FeePaymentRequest;
use App\Http\Requests\UpdateFeePaymentRequest;
use App\Http\Resources\FeeDebtorResource;
use App\Http\Requests\PayRegistrationFeesRequest;
use App\Http\Resources\PaidFeesResource;
use Illuminate\Http\Request;

class FeePaymentController extends Controller
{
    //
    protected FeePaymentService $feePaymentService;
    public function __construct(FeePaymentService $feePaymentService){
        $this->feePaymentService = $feePaymentService;
    }
    public function payTuitionFees(FeePaymentRequest $request) {
        $currentSchool = $request->attributes->get('currentSchool');
        $payFees = $this->feePaymentService->payStudentFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Fees Paid Sucessfully", $payFees, null, 201);
    }

    public function payRegistrationFees(PayRegistrationFeesRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $payRegistrationFees = $this->feePaymentService->payRegistrationFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Registration Fees Paid Sucessfully", $payRegistrationFees, null, 201);
    }

    public function getFeesPaid(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $feePaid = $this->feePaymentService->getFeesPaid($currentSchool);
        return ApiResponseService::success('fee payment records fetched successfully', PaidFeesResource::collection($feePaid), null, 200);
    }

    public function updateFeesPaid(UpdateFeePaymentRequest $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $updateFeesPaid = $this->feePaymentService->updateStudentFeesPayment($request->validated(),$fee_id,$currentSchool);
        return ApiResponseService::success("Fee Payment Record Updated Sucessfully", $updateFeesPaid, null, 200);
    }

    public function deleteFeePaid(Request $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteFeePayment = $this->feePaymentService->deleteFeePayment($fee_id, $currentSchool);
        return ApiResponseService::success('Record Deleted Sucessfully', $deleteFeePayment, null, 200);
    }

    public function getFeeDebtors(Request $requst){
        $currentSchool = $requst->attributes->get('currentSchool');
        $feeDebtors = $this->feePaymentService->getFeeDebtors($currentSchool);
        return ApiResponseService::success("Fee Debtors Fetched Succefully", FeeDebtorResource::collection($feeDebtors), null, 200);
    }
}
