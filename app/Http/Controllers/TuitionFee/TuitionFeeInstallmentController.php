<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeeInstallmentService;
use App\Http\Requests\FeeInstallment\CreateFeeInstallment;
use App\Http\Requests\FeeInstallment\UpdateFeeInstallment;
use App\Services\ApiResponseService;

class TuitionFeeInstallmentController extends Controller
{
    protected TuitionFeeInstallmentService $tuitionFeeInstallmentService;

    public function __construct(TuitionFeeInstallmentService $tuitionFeeInstallmentService)
    {
        $this->tuitionFeeInstallmentService = $tuitionFeeInstallmentService;
    }


    //add other methods left
    public function createFeeInstallment(CreateFeeInstallment $request)
    {
        $this->tuitionFeeInstallmentService->createInstallment($request->validated());
        return ApiResponseService::success("Fee Installment Created Successfully", null, null, 201);
    }

    public function deleteFeeInstallment($installmentId)
    {
        $this->tuitionFeeInstallmentService->deleteInstallment($installmentId);
        return ApiResponseService::success("Fee Installment Deleted Successfully", null, null, 200);
    }

    public function getFeeInstallment()
    {
        $feeInstallments = $this->tuitionFeeInstallmentService->getInstallments();
        return ApiResponseService::success("Fee Installment Fetched Successfully", $feeInstallments, null, 200);
    }

    public function updateFeeInstallment(UpdateFeeInstallment $request, $installmentId)
    {
        $this->tuitionFeeInstallmentService->updateInstallment($request->validated(), $installmentId);
        return ApiResponseService::success("Fee Installment Updated Successfully", null, null, 200);
    }

    public function getActiveFeeInstallment()
    {
        $activeFeeInstallment = $this->tuitionFeeInstallmentService->getInstallments();
        return ApiResponseService::success("Fee Installments Fetched Successfully", $activeFeeInstallment, null, 200);
    }
}
