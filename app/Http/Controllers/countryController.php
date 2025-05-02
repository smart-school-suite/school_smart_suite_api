<?php

namespace App\Http\Controllers;

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
    public function createCountry(CreateCountryRequest $request)
    {

        $country = $this->countryService->createCountry($request->validated());
        return ApiResponseService::success('Country Created sucessfully', $country, null, 200);
    }

    public function updateCountry(UpdateCountryRequest $request, string $country_id)
    {
        $updatedCountry = $this->countryService->updateCountry($request->validated(), $country_id);
        return ApiResponseService::success('Country Updated sucessfully', $updatedCountry, null, 200);
    }

    public function deleteCountry(string $country_id)
    {
        $country = Country::find($country_id);
        $deleteCountry = $this->countryService->deleteCountry($country_id);
        return ApiResponseService::success('Country Delete succesfully', $deleteCountry, null, 200);
    }

    public function getCountries()
    {
        $country = $this->countryService->getCountries();
        return ApiResponseService::success('Countries Fetched Succefully', $country, null, 200);
    }

    public function bulkUpdateCountry(BulkUpdateCountryRequest $request){
        try{
           $bulkUpdateCountry = $this->countryService->bulkUpdateCountry($request->countries);
           return  ApiResponseService::success("Country Updated Successfully", $bulkUpdateCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteCountry($countryIds){
        $idsArray = explode(',', $countryIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:country,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeleteCountry = $this->countryService->bulkDeleteCountry($idsArray);
           return ApiResponseService::success("Country Deleted Successfully", $bulkDeleteCountry, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
