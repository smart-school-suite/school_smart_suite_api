<?php

namespace App\Http\Controllers\Resit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Resit\ResitPaymentService;
use App\Http\Requests\StudentResit\StudentResitTransactionIdRequest;
use Exception;
use App\Http\Requests\StudentResit\BulkPayStudentResitRequest;
use App\Http\Requests\StudentResit\PayResitFeeRequest;
use App\Http\Resources\StudentResitTransResource;
use App\Services\ApiResponseService;

class ResitPaymentController extends Controller
{
    protected ResitPaymentService $resitPaymentService;
    public function __construct(ResitPaymentService $resitPaymentService)
    {
        $this->resitPaymentService = $resitPaymentService;
    }

    public function payResit(PayResitFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $payStudentResit = $this->resitPaymentService->payResit($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Resit Paid Successfully", $payStudentResit, null, 200);
    }
    public function getResitPaymentTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $getResitTransactions = $this->resitPaymentService->getResitPaymentTransactions($currentSchool);
        return ApiResponseService::success("Student Resit Payment Transactions Fetched Succefully", StudentResitTransResource::collection($getResitTransactions), null, 200);
    }
    public function deleteFeePaymentTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTransaction = $this->resitPaymentService->deleteResitFeeTransaction($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Deleted Succesfully", $deleteTransaction, null, 200);
    }
    public function getTransactionDetails(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $transactionDetails = $this->resitPaymentService->getTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Details Fetched Succesfully", $transactionDetails, null, 200);
    }
    public function reverseTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $reverseTransaction = $this->resitPaymentService->reverseResitTransaction($transactionId, $currentSchool);
        return ApiResponseService::success("Transaction Reversed Succesfully", $reverseTransaction, null, 200);
    }
    public function bulkPayStudentResit(BulkPayStudentResitRequest $request)
    {
        try {
            $currentSchool = $request->attributes->get("currentSchool");
            $bulkPayStudentResit = $this->resitPaymentService->bulkPayStudentResit($request->paymentData, $currentSchool);
            return ApiResponseService::success("Student Resit Paid Succesfully", $bulkPayStudentResit, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteStudentResitTransactions(StudentResitTransactionIdRequest $request)
    {
        try {
            $bulkDeletResitTransactions = $this->resitPaymentService->bulkDeleteTransaction($request->transactionIds);
            return ApiResponseService::success("Student Resit Transactions Deleted Succefully", $bulkDeletResitTransactions, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkReverseTransaction(StudentResitTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        try {
            $bulkReverseTransaction = $this->resitPaymentService->bulkReverseResitTransaction($request->transactionIds, $currentSchool);
            return ApiResponseService::success("Transactions Reversed Succesfully", $bulkReverseTransaction, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
