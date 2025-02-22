<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use App\http\Requests\CountryRequest;
use App\http\Requests\UpdateCountryRequest;
use App\Services\ApiResponseService;


class CountryController extends Controller
{
    //

    protected CountryService $countryService;
    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }
    public function create_country(CountryRequest $request)
    {

        $country = $this->countryService->createCountry($request->validated());
        return ApiResponseService::success('Country Created sucessfully', $country, null, 200);
    }

    public function update_country(UpdateCountryRequest $request, string $country_id)
    {
        $updatedCountry = $this->countryService->updateCountry($request->validated(), $country_id);
        return ApiResponseService::success('Country Updated sucessfully', $updatedCountry, null, 200);
    }

    public function delete_country(string $country_id)
    {
        $country = Country::find($country_id);
        $deleteCountry = $this->countryService->deleteCountry($country_id);
        return ApiResponseService::success('Country Delete succesfully', $deleteCountry, null, 200);
    }

    public function get_all_countries()
    {
        $country = $this->countryService->getCountries();
        return ApiResponseService::success('Countries Fetched Succefully', $country, null, 200);
    }
}
