<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Http\Requests\SubscriptionRate\CreateSubscriptionRateRequest;
use App\Http\Requests\SubscriptionRate\UpdateSubscriptionRateRequest;
use App\Services\Subscription\SubscriptionRateCardService;

class RateCardController extends Controller
{
        protected SubscriptionRateCardService $ratesCardService;
     public function __construct(SubscriptionRateCardService $ratesCardService){
        $this->ratesCardService = $ratesCardService;
     }
    public function createRates(CreateSubscriptionRateRequest $request)
    {
        $createRateCard = $this->ratesCardService->createRate($request->validated());
        return ApiResponseService::success("Rate Card Created Succesfully", $createRateCard, null, 201);
    }

    public function updatRates(UpdateSubscriptionRateRequest $request,  $rateId){
         $updateRateCard = $this->ratesCardService->updateRate($rateId, $request->validated());
         return ApiResponseService::success("Rate Card Updated Sucessfully", $updateRateCard, null, 200);
    }

    public function deleteRates($rateId){
        $deleteRateCard = $this->ratesCardService->deleteRate($rateId);
        return ApiResponseService::success("Rate Card Deleted Succefully", $deleteRateCard, null, 200);
    }

    public function getAllRates(){
        $getRates = $this->ratesCardService->getAllRates();
        return ApiResponseService::success("Rate Card Data Fetched Sucessfully", $getRates, null, 200);
    }
}
