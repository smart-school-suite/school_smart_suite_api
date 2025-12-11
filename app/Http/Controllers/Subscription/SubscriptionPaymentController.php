<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Subscription\SubscriptionPaymentService;
use Illuminate\Http\Request;
class SubscriptionPaymentController extends Controller
{
    protected SubscriptionPaymentService $subscriptionPaymentService;
    public function __construct(SubscriptionPaymentService $subscriptionPaymentService)
    {
        $this->subscriptionPaymentService = $subscriptionPaymentService;
    }

    public function getTransactions(Request $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $transactions = $this->subscriptionPaymentService->getSubscriptionTransactions($currentSchool);
         return ApiResponseService::success("Transactions Fetched Successfully", $transactions, null, 200);
    }

    public function deleteTransaction(Request $request, $transactionId){
         $currentSchool = $request->attributes->get('currentSchool');
         $deleteTransaction = $this->subscriptionPaymentService->deleteTransaction($currentSchool, $transactionId);
         return ApiResponseService::success("Transaction Deleted Successfully", $deleteTransaction, null, 200);
    }

    public function getTransactionDetails(Request $request, $transactionId){
        $currentSchool = $request->attributes->get('currentSchool');
        $transactionDetails = $this->subscriptionPaymentService->getTransactionDetails($currentSchool, $transactionId);
        return ApiResponseService::success("Transaction Details Fetched Successfully", $transactionDetails, null, 200);
    }
}
