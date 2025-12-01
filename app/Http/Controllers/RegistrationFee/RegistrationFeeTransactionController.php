<?php

namespace App\Http\Controllers\RegistrationFee;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationFee\RegistrationFeeTransactionIdRequest;
use Illuminate\Http\Request;
use App\Services\RegistrationFee\RegistrationFeeTransactionService;
use App\Services\ApiResponseService;

class RegistrationFeeTransactionController extends Controller
{
    protected RegistrationFeeTransactionService $registrationFeeTransactionService;

    public function __construct(RegistrationFeeTransactionService $registrationFeeTransactionService)
    {
        $this->registrationFeeTransactionService = $registrationFeeTransactionService;
    }
    public function bulkDeleteRegistrationFeeTransactions(RegistrationFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkDelete = $this->registrationFeeTransactionService->bulkDeleteRegistrationFeeTransactions($request->transactionIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transactions Deleted Succesfully", $bulkDelete, null, 200);
    }
    public function reverseRegistrationFeePaymentTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $reverseTransaction = $this->registrationFeeTransactionService->reverseRegistrationFeePaymentTransaction($transactionId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transaction Reversed Sucessfully", $reverseTransaction, null, 200);
    }
    public function getRegistrationFeeTransactions(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $registrationFeeTransaction = $this->registrationFeeTransactionService->getRegistrationFeeTransactions($currentSchool);
        return ApiResponseService::success("Registration Fee Transactions Fetched Successfully", $registrationFeeTransaction, null, 200);
    }
    public function getRegistrationFeeTransactionDetails(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetails = $this->registrationFeeTransactionService->getRegistrationFeeTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Registration Fee Transaction Details Fetched Successfully", $transactionDetails, null, 200);
    }
    public function deleteRegistrationFeeTransaction(Request $request, $transactionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->registrationFeeTransactionService->deleteRegistrationFeeTransaction($currentSchool, $transactionId, $authAdmin);
        return ApiResponseService::success("Registration Transaction Deleted Successfully", null, null, 200);
    }
    public function bulkReverseRegistrationFeeTransaction(RegistrationFeeTransactionIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkReverseTransaction = $this->registrationFeeTransactionService->bulkReverseRegistrationFeeTransaction($request->transactionIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Transaction Reversed Successfully", $bulkReverseTransaction, null, 200);
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
