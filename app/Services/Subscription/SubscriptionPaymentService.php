<?php

namespace App\Services\Subscription;

use App\Exceptions\AppException;
use App\Models\SchoolTransaction;

class SubscriptionPaymentService
{
    public function getSubscriptionTransactions($currentSchool)
    {
        $transactions = SchoolTransaction::where("school_branch_id", $currentSchool->id)
            ->where('type', "subscription_purchase")
            ->with(['country'])
            ->get();
        return $transactions;
    }

    public function getTransactionDetails($currrentSchool, $transactionId)
    {
        $transaction = SchoolTransaction::where("school_branch_id", $currrentSchool->id)
            ->where("type", "subscription_purchase")
            ->where("id", $transactionId)
            ->firstOrFail();
        return $transaction;
    }

    public function deleteTransaction($currentSchool, $transactionId)
    {
        $transaction = SchoolTransaction::where("school_branch_id", $currentSchool->id)
            ->find($transactionId);
        if (!$transaction) {
            throw new AppException(
                "Transaction Not Found",
                404,
                "Transaction Not Found",
                "Transaction Not Found, the transaction might have been deleted please try or contact customer support"
            );
        }

        $transaction->delete();
        return $transaction;
    }

}
