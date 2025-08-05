<?php

namespace App\Services;
use Exception;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
class CountryManagementService
{

        public function createCountry(array $data)
    {
        $country = new Country();
        $country->country = $data["country"];
        $country->official_language = $data["official_language"];
        $country->currency = $data["currency"];
        $country->code = $data["code"];
        $country->save();
        return $country;
    }

    public function updateCountry(array $data, string $countryId)
    {
        $country = Country::find($countryId);
        if (!$country) {
            return ApiResponseService::error('Country Not found', null, 404);
        }
        $filteredData = array_filter($data);
        $country->update($filteredData);
        return $country;
    }

    public function bulkUpdateCountry(array $updateCountryList){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($updateCountryList as $updateCountry){
               $country = Country::findOrFail($updateCountry['country_id']);
               $filteredData = array_filter($updateCountry);
               $country->update($filteredData);
               $result[] = $country;
            }
            DB::commit();
            return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCountry(string $countryId)
    {
        $country = Country::find($countryId);
        if (!$country) {
            return ApiResponseService::error('Country Not found', null, 404);
        }
        $country->delete();
        return $country;
    }

    public function bulkDeleteCountry($countryIds){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($countryIds as $countryId){
                $country = Country::findOrFail($countryId['country_id']);
                $country->delete();
                $result[] = $country;
            }
            DB::commit();
            return $result;
        }
        catch(Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getCountries()
    {
        $countries = Country::where("status", true)->get();
        return $countries;
    }
}
