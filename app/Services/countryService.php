<?php

namespace App\Services;

use App\Models\Country;

class countryService
{
    // Implement your logic here

    public function createCountry(array $data) : Country {
        $country = new Country();

        $country->country = $data["country"];

        $country->save();
        return $country;
    }

    public function updateCountry(array $data, string $country_id) :? Country {
         $country = Country::find($country_id);
         if(!$country){
            return null;
         }

         $filteredData = array_filter($data);

         $country->update($filteredData);

         return $country;
    }

    public function deleteCountry(string $country_id) : bool {
         $country = Country::find($country_id);
         if(!$country){
            return false;
         }
         $country->delete();
         return true;
    }

    public function getCountries()  {
        $countries = Country::all();
        return $countries;
    }
}
