<?php

namespace App\Http\Controllers;

use App\Http\Requests\RateCardRequest;
use App\Services\ApiResponseService;
use App\Services\RateCardService;
use Illuminate\Http\Request;

class RatesCardController extends Controller
{
     protected RateCardService $ratesCardService;
     public function __construct(RateCardService $ratesCardService){
        $this->ratesCardService = $ratesCardService;
     }
    public function create_rates(RateCardRequest $request)
    {
        $createRateCard = $this->ratesCardService->createRate($request->validated());
        return ApiResponseService::success("Rate Card Created Succesfully", $createRateCard, null, 201);
    }

    public function update_rates_card(Request $request, $rates_id){
         $updateRateCard = $this->ratesCardService->updateRate($rates_id, $request->validated());
         return ApiResponseService::success("Rate Card Updated Sucessfully", $updateRateCard, null, 200);
    }

    public function delete_rate(Request $request, $rate_id){
        $deleteRateCard = $this->ratesCardService->deleteRate($rate_id);
        return ApiResponseService::success("Rate Card Deleted Succefully", $deleteRateCard, null, 200);
    }

    public function get_rates(Request $request){
        $getRates = $this->ratesCardService->getAllRates();
        return ApiResponseService::success("Rate Card Data Fetched Sucessfully", $getRates, null, 200);
    }
}
