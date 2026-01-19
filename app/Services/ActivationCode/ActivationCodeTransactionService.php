<?php

namespace App\Services\ActivationCode;
use App\Exceptions\AppException;
use App\Models\SchoolTransaction;
class ActivationCodeTransactionService
{
    public function getTransactions($currentSchool){
        $transactions = SchoolTransaction::where("school_branch_id", $currentSchool->id)
                            ->where("type", "activation_code_purchase")
                            ->get();
        if($transactions->isEmpty()){
            throw new AppException(
                 "No Transactions Found",
                 404,
                 "No Transactions Found",
                 "No Transactions Found In Order to view transaction you must make purchase of activation codes"
            );
        }

        return $transactions->map(fn($transaction) => [
             "id" => $transaction->id,
             "type" => "Activation Code",
            "amount" => $transaction->amount,
            "payment_ref" => $transaction->payment_ref,
            "transaction_id" => $transaction->transaction_id,
            "status" => $transaction->status,
            "created_at" => $transaction->created_at,
        ]);
    }

}
