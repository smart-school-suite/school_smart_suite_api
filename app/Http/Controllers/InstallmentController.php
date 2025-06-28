<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeInstallment\CreateFeeInstallment;
use App\Http\Requests\FeeInstallment\UpdateFeeInstallment;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\InstallmentService;
class InstallmentController extends Controller
{
    protected InstallmentService $installmentService;
    public function __construct(InstallmentService $installmentService){
        $this->installmentService = $installmentService;
    }

    //add other methods left
    public function createFeeInstallment(CreateFeeInstallment $request){
       $this->installmentService->createInstallment($request->validated());
       return ApiResponseService::success("Fee Installment Created Successfully", null, null, 201);
    }

    public function deleteFeeInstallment($installmentId){
        $this->installmentService->deleteInstallment($installmentId);
        return ApiResponseService::success("Fee Installment Deleted Successfully", null, null, 200);
    }

    public function getFeeInstallment(){
        $feeInstallments = $this->installmentService->getInstallments();
        return ApiResponseService::success("Fee Installment Fetched Successfully", $feeInstallments, null, 200);
    }

    public function updateFeeInstallment(UpdateFeeInstallment $request, $installmentId) {
        $this->installmentService->updateInstallment($request->validated(), $installmentId);
        return ApiResponseService::success("Fee Installment Updated Successfully", null, null, 200);
    }

    public function getActiveFeeInstallment(){
        $activeFeeInstallment = $this->installmentService->getInstallments();
        return ApiResponseService::success("Fee Installments Fetched Successfully", $activeFeeInstallment, null, 200);
    }

}
