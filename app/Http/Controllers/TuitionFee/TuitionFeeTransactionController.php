<?php

namespace App\Http\Controllers\TuitionFee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TuitionFee\TuitionFeeTransactionService;
use App\Http\Resources\TuitionFeeTransacResource;
use App\Services\ApiResponseService;
use App\Http\Requests\TuitionFee\TuitionFeeTransactionIdRequest;
use Exception;

class TuitionFeeTransactionController extends Controller
{
    protected TuitionFeeTransactionService $tuitionFeeTransactionService;
    public function __construct(TuitionFeeTransactionService $tuitionFeeTransactionService)
    {
        $this->tuitionFeeTransactionService = $tuitionFeeTransactionService;
    }
    public function getTuitionFeeTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactions = $this->tuitionFeeTransactionService->getTuitionFeeTransactions($currentSchool);
        return ApiResponseService::success("Tuition Fee Transactions fetched Successfully", TuitionFeeTransacResource::collection($transactions), null, 200);
    }
    public function reverseTuitionFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $reverseTransaction = $this->tuitionFeeTransactionService->reverseFeePaymentTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }
    public function deleteTuitionFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->tuitionFeeTransactionService->deleteTuitionFeeTransaction($transactionId, $currentSchool);
    }
    public function getTuitionTransactionFeeDetails(Request $request, $tranctionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetail = $this->tuitionFeeTransactionService->tuitionFeeTransactionDetails($tranctionId, $currentSchool);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetail, null, 200);
    }
    public function bulkReverseTuitionFeeTransaction(TuitionFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $bulkReverseTransaction = $this->tuitionFeeTransactionService->bulkReverseTuitionFeeTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteTuitionFeeTransactions(TuitionFeeTransactionIdRequest $request)
    {
        try {
            $bulkDelete = $this->tuitionFeeTransactionService->bulkDeleteTuitionFeeTransaction($request->transactionIds);
            return ApiResponseService::success("Tuition Fee Transactions Deleted Successfully", $bulkDelete, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
