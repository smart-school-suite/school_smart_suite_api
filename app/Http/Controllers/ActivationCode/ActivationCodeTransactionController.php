<?php

namespace App\Http\Controllers\ActivationCode;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ActivationCode\ActivationCodeTransactionService;
use App\Services\ApiResponseService;

class ActivationCodeTransactionController extends Controller
{
   protected ActivationCodeTransactionService $activationCodeTransactionService;
   public function __construct(ActivationCodeTransactionService $activationCodeTransactionService)
   {
     $this->activationCodeTransactionService = $activationCodeTransactionService;
   }

   public function getActivationCodeTransactions(Request $request){
      $currentSchool = $request->attributes->get("currentSchool");
      $transactions = $this->activationCodeTransactionService->getTransactions($currentSchool);
      return ApiResponseService::success("Transactions Fetched Successfully", $transactions, null, 200);
   }
}
