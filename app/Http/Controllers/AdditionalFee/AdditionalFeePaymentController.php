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
        $authAdmin = $this->resolveUser();
        $payAdditionalFees = $this->additionalFeePaymentService->payAdditionalFees($request->validated(), $currentSchool, $authAdmin);
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
        $authAdmin = $this->resolveUser();
        $reverseTransaction = $this->additionalFeePaymentService->reverseTransaction($transactionId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transaction Reversed Successfully", $reverseTransaction, null, 200);
    }

    public function deleteTransaction(Request $request, string $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $deleteTransaction = $this->additionalFeePaymentService->deleteTransaction($transactionId, $currentSchool, $authAdmin);
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
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkDeleteTransaction = $this->additionalFeePaymentService->bulkDeleteTransaction($request->transactionIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transaction Deleted Succesfully", $bulkDeleteTransaction, null, 200);
    }

    public function bulkReverseTransaction(AdditionalFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkReverseTransaction = $this->additionalFeePaymentService->bulkReverseTransaction($request->transactionIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
    }

    public function bulkPayFees(BulkPayAdditionalFeeRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkPayFees = $this->additionalFeePaymentService->bulkPayAdditionalFee($request->additional_fee, $currentSchool, $authAdmin);
        return ApiResponseService::success("Additional Fees Paid Succesfully", $bulkPayFees, null, 200);
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
