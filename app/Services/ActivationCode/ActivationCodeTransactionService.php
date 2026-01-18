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

        return $transactions;
    }

}
