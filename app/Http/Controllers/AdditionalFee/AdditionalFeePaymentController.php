<?php

namespace App\Http\Controllers\AdditionalFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdditionalFee\AdditionalFeeTransactionIdRequest;
use App\Http\Requests\AdditionalFee\BulkPayAdditionalFeeRequest;
use App\Http\Requests\AdditionalFee\PayAdditionalFeeRequest;
use App\Services\ApiResponseService;
use App\Http\Resources\AdditionalFeeTransactionResource;
use App\Services\AdditionalFee\AdditionalFeePaymentService;
use Exception;
use Illuminate\Http\Request;

class AdditionalFeePaymentController extends Controller
{
    protected AdditionalFeePaymentService $additionalFeePaymentService;
    public function __construct(
        AdditionalFeePaymentService $additionalFeePaymentService,
    ) {
        $this->additionalFeePaymentService = $additionalFeePaymentService;
    }

    public function payAdditionalFees(PayAdditionalFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payAdditionalFees = $this->additionalFeePaymentService->payAdditionalFees($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Additional Fees Paid Successfully", $payAdditionalFees, null, 201);
    }

    public function getAdditionalFeesTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAdditionalFeesTransactions = $this->additionalFeePaymentService->getAdditionalFeesTransactions($currentSchool);
        return ApiResponseService::success("Student Additional Fees Transactions Fetched Sucessfully", AdditionalFeeTransactionResource::collection($getAdditionalFeesTransactions), null, 200);
    }

    public function reverseAdditionalFeesTransaction(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->additionalFeePaymentService->reverseTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }

    public function deleteTransaction(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTransaction = $this->additionalFeePaymentService->deleteTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Deleted Successfully", $deleteTransaction, null, 200);
    }

    public function getTransactionDetails(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetails = $this->additionalFeePaymentService->getTransactionDetail($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetails, null, 200);
    }

    public function bulkDeleteTransaction(AdditionalFeeTransactionIdRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get('currentSchool');
            $bulkDeleteTransaction = $this->additionalFeePaymentService->bulkDeleteTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Deleted Succesfully", $bulkDeleteTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkReverseTransaction(AdditionalFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkReverseTransaction = $this->additionalFeePaymentService->bulkReverseTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkPayFees(BulkPayAdditionalFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkPayFees = $this->additionalFeePaymentService->bulkPayAdditionalFee($request->additional_fee, $currentSchool);
            return ApiResponseService::success("Additional Fees Paid Succesfully", $bulkPayFees, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
