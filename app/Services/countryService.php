<?php

namespace App\Services;

use App\Models\Country;

class countryService
{
    // Implement your logic here

    public function createCountry(array $data)
    {
        $country = new Country();
        $country->country = $data["country"];
        $country->status = $data["status"];
        $country->code = $data["code"];
        $country->save();
        return $country;
    }

    public function updateCountry(array $data, string $country_id)
    {
        $country = Country::find($country_id);
        if (!$country) {
            return ApiResponseService::error('Country Not found', null, 404);
        }
        $filteredData = array_filter($data);
        $country->update($filteredData);
        return $country;
    }

    public function deleteCountry(string $country_id)
    {
        $country = Country::find($country_id);
        if (!$country) {
            return ApiResponseService::error('Country Not found', null, 404);
        }
        $country->delete();
        return $country;
    }

    public function getCountries()
    {
        $countries = Country::where("status", true)->get();
        return $countries;
    }
}
