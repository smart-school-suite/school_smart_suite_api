<?php

namespace App\Services;
use App\Models\SubscriptionPayment;
class SubscriptionPaymentService
{
    // Implement your logic here

    public function deletePaymentTransaction($transactionId){
        $findTransaction = SubscriptionPayment::findOrFail($transactionId);
        $findTransaction->delete();
        return $findTransaction;
    }

    public function myTransactions($school_id){
        $myTransactions = SubscriptionPayment::where("school_id", $school_id)->with(['schoolSubscription'])->get();
        return $myTransactions;
    }

    public function getAllTransactions(){
        $transactions = SubscriptionPayment::with(['schoolSubscription'])->get();
        return $transactions;
    }

}
