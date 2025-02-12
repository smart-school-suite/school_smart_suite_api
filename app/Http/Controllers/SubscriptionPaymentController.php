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
    public function delete_payment($transaction_id)
    {
        $deleteTransaction = $this->subscriptionPaymentService->deletePaymentTransaction($transaction_id);
        return ApiResponseService::success("Transaction Deleted Sucessfully", $deleteTransaction, null, 200);
    }

    public function my_transactions($school_id)
    {
        $myTransactions = $this->subscriptionPaymentService->myTransactions($school_id);
        return ApiResponseService::success("Transaction Fetched Succefully", $myTransactions, null, 200);
    }

    public function get_all_transactions()
    {
        $getAllTransactions = $this->subscriptionPaymentService->getAllTransactions();
        return ApiResponseService::success("Transactions Fetched Successfully", $getAllTransactions, null, 200);
    }
}
