<?php

namespace App\Http\Controllers;


use App\Services\ApiResponseService;
use App\Http\Requests\SubscriptionRate\BulkUpdateSubscriptionRateRequest;
use App\Http\Requests\SubscriptionRate\CreateSubscriptionRateRequest;
use App\Http\Requests\SubscriptionRate\UpdateSubscriptionRateRequest;
use App\Services\RateCardService;
use Illuminate\Http\Request;

class RatesCardController extends Controller
{
     protected RateCardService $ratesCardService;
     public function __construct(RateCardService $ratesCardService){
        $this->ratesCardService = $ratesCardService;
     }
    public function createRates(CreateSubscriptionRateRequest $request)
    {
        $createRateCard = $this->ratesCardService->createRate($request->validated());
        return ApiResponseService::success("Rate Card Created Succesfully", $createRateCard, null, 201);
    }

    public function updatRates(UpdateSubscriptionRateRequest $request,  $rates_id){
         $updateRateCard = $this->ratesCardService->updateRate($rates_id, $request->validated());
         return ApiResponseService::success("Rate Card Updated Sucessfully", $updateRateCard, null, 200);
    }

    public function deleteRates($rate_id){
        $deleteRateCard = $this->ratesCardService->deleteRate($rate_id);
        return ApiResponseService::success("Rate Card Deleted Succefully", $deleteRateCard, null, 200);
    }

    public function getAllRates(){
        $getRates = $this->ratesCardService->getAllRates();
        return ApiResponseService::success("Rate Card Data Fetched Sucessfully", $getRates, null, 200);
    }
}
