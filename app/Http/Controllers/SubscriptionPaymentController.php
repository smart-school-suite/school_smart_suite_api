<?php

namespace App\Http\Controllers;


use App\Services\SubscriptionPaymentService;
use App\Services\ApiResponseService;

class SubscriptionPaymentController extends Controller
{
    //
    protected SubscriptionPaymentService $subscriptionPaymentService;
    public function __construct(SubscriptionPaymentService $subscriptionPaymentService)
    {
        $this->subscriptionPaymentService = $subscriptionPaymentService;
    }
    public function deletePayment($transactionId)
    {
        $deleteTransaction = $this->subscriptionPaymentService->deletePaymentTransaction($transactionId);
        return ApiResponseService::success("Transaction Deleted Sucessfully", $deleteTransaction, null, 200);
    }

    public function getTransactionsBySchool($schoolId)
    {
        $myTransactions = $this->subscriptionPaymentService->myTransactions($schoolId);
        return ApiResponseService::success("Transaction Fetched Succefully", $myTransactions, null, 200);
    }

    public function getAllTransactions()
    {
        $getAllTransactions = $this->subscriptionPaymentService->getAllTransactions();
        return ApiResponseService::success("Transactions Fetched Successfully", $getAllTransactions, null, 200);
    }
}
