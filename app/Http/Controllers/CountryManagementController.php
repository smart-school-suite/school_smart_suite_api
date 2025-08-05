<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CountryManagementService;
use App\Http\Requests\Country\CountryIdsRequest;
use App\Http\Requests\Country\CreateCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;
use App\Http\Requests\Country\BulkUpdateCountryRequest;
use App\Services\ApiResponseService;
use Exception;

class CountryManagementController extends Controller
{
    protected CountryManagementService $countryManagementService;
    public function __construct(CountryManagementService $countryManagementService)
    {
        $this->countryManagementService = $countryManagementService;
    }
    public function createCountry(CreateCountryRequest $request){
        $country = $this->countryManagementService->createCountry($request->validated());
        return ApiResponseService::success('Country Created sucessfully', $country, null, 201);
    }
    public function updateCountry(UpdateCountryRequest $request, string $countryId){
        $updatedCountry = $this->countryManagementService->updateCountry($request->validated(), $countryId);
        return ApiResponseService::success('Country Updated sucessfully', $updatedCountry, null, 200);
    }
    public function deleteCountry(string $countryId){
        $deleteCountry = $this->countryManagementService->deleteCountry($countryId);
        return ApiResponseService::success('Country Delete succesfully', $deleteCountry, null, 200);
    }
    public function getCountries(){
        $country = $this->countryManagementService->getCountries();
        return ApiResponseService::success('Countries Fetched Succefully', $country, null, 200);
    }
    public function bulkUpdateCountry(BulkUpdateCountryRequest $request){
        try{
           $bulkUpdateCountry = $this->countryManagementService->bulkUpdateCountry($request->country);
           return  ApiResponseService::success("Country Updated Successfully", $bulkUpdateCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteCountry(CountryIdsRequest $request){
        try{
           $bulkDeleteCountry = $this->countryManagementService->bulkDeleteCountry($request->countryIds);
           return ApiResponseService::success("Country Deleted Successfully", $bulkDeleteCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
