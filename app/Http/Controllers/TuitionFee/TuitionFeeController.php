<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeeService;
use App\Services\ApiResponseService;
use App\Http\Resources\TuitionFeeResource;
use App\Http\Resources\FeeDebtorResource;

class TuitionFeeController extends Controller
{
    protected TuitionFeeService $tuitionFeeService;
    public function __construct(TuitionFeeService $tuitionFeeService)
    {
        $this->tuitionFeeService = $tuitionFeeService;
    }
    public function getTuitionFeeDetails(Request $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $tuitionFeeDetails = $this->tuitionFeeService->getTuitionFeeDetails($currentSchool, $feeId);
        return ApiResponseService::success("Tuition Fee Details Fetched Successfully", $tuitionFeeDetails, null, 200);
    }
    public function getFeesPaid(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feePaid = $this->tuitionFeeService->getFeesPaid($currentSchool);
        return ApiResponseService::success('fee payment records fetched successfully', $feePaid, null, 200);
    }
    public function deleteFeePaid(Request $request, $feeId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deleteFeePayment = $this->tuitionFeeService->deleteFeePayment($feeId, $currentSchool, $authAdmin);
        return ApiResponseService::success('Record Deleted Sucessfully', $deleteFeePayment, null, 200);
    }

    public function getFeeDebtors(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $feeDebtors = $this->tuitionFeeService->getFeeDebtors($currentSchool);
        return ApiResponseService::success("Fee Debtors Fetched Succefully", FeeDebtorResource::collection($feeDebtors), null, 200);
    }

    public function getTuitionFees(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $tuitionFees = $this->tuitionFeeService->getTuitionFees($currentSchool);
        return ApiResponseService::success("Tuition Fees Fetched Successfully", TuitionFeeResource::collection($tuitionFees), null, 200);
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
