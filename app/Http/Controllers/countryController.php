<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class countryController extends Controller
{
    //

    protected CountryService $countryService;
    public function __construct(CountryService $countryService){
        $this->countryService = $countryService;
    }
    public function create_country(Request $request){
         $request->validate([
           'country' => 'required|string'
         ]);

         $country = $this->countryService->createCountry($request->validated());
         return ApiResponseService::success('Country Created sucessfully', $country, null, 200);

    }

    public function update_country(Request $request, string $country_id){
         $updatedCountry = $this->countryService->updateCountry($request->validated(), $country_id);
         if($updatedCountry == null){
            return ApiResponseService::error('Country Not found', null, 404);
         }
         return ApiResponseService::success('Country Updated sucessfully', $updatedCountry, null, 200);
    }

    public function delete_country( string $country_id){
        $country = Country::find($country_id);
        $deleteCountry = $this->countryService->deleteCountry($country_id);
        if(!$country){
           return ApiResponseService::error('Country not found', null,404);
        }
        return ApiResponseService::success('Country Delete succesfully', $deleteCountry, null, 200);

    }

    public function get_all_countries(Request $request){
        $country = $this->countryService->getCountries();
        if($country->isEmpty()){
            return ApiResponseService::error('Looks Like Country Collection is empty', null,409);
        }

        return ApiResponseService::success('Countries Fetched Succefully', $country, null, 200);
    }


}
