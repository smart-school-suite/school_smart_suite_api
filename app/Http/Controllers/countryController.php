<?php

namespace App\Http\Controllers;

use App\Http\Requests\Country\CountryIdsRequest;
use App\Models\Country;
use App\Services\CountryService;
use App\http\Requests\Country\CreateCountryRequest;
use App\http\Requests\Country\UpdateCountryRequest;
use App\http\Requests\Country\BulkUpdateCountryRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;
use Exception;


class CountryController extends Controller
{
    //

    protected CountryService $countryService;
    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }
    public function createCountry(CreateCountryRequest $request){
        $country = $this->countryService->createCountry($request->validated());
        return ApiResponseService::success('Country Created sucessfully', $country, null, 201);
    }
    public function updateCountry(UpdateCountryRequest $request, string $countryId){
        $updatedCountry = $this->countryService->updateCountry($request->validated(), $countryId);
        return ApiResponseService::success('Country Updated sucessfully', $updatedCountry, null, 200);
    }
    public function deleteCountry(string $countryId){
        $country = Country::find($countryId);
        $deleteCountry = $this->countryService->deleteCountry($countryId);
        return ApiResponseService::success('Country Delete succesfully', $deleteCountry, null, 200);
    }
    public function getCountries(){
        $country = $this->countryService->getCountries();
        return ApiResponseService::success('Countries Fetched Succefully', $country, null, 200);
    }
    public function bulkUpdateCountry(BulkUpdateCountryRequest $request){
        try{
           $bulkUpdateCountry = $this->countryService->bulkUpdateCountry($request->country);
           return  ApiResponseService::success("Country Updated Successfully", $bulkUpdateCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteCountry(CountryIdsRequest $request){
        try{
           $bulkDeleteCountry = $this->countryService->bulkDeleteCountry($request->countryIds);
           return ApiResponseService::success("Country Deleted Successfully", $bulkDeleteCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
