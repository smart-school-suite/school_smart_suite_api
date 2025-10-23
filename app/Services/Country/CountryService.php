<?php

namespace App\Services\Country;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AppException;
use Throwable;
use Exception;

class CountryService
{
    public function createCountry(array $data)
    {

        $countryName = $data["country"];
        $countryCode = $data["code"];

        if (Country::where('country', $countryName)->exists()) {
            throw new AppException(
                "A country with the name '{$countryName}' already exists.",
                409,
                "Duplicate Country Name 🌍",
                "A country with this exact name is already recorded. Please verify the name.",
                null
            );
        }
        if (Country::where('code', $countryCode)->exists()) {
            throw new AppException(
                "A country with the code '{$countryCode}' already exists.",
                409,
                "Duplicate Country Code 🏷️",
                "A country with this exact code is already recorded. Please choose a unique code.",
                null
            );
        }

        try {

            $country = new Country();
            $country->country = $countryName;
            $country->official_language = $data["official_language"];
            $country->currency = $data["currency"];
            $country->code = $countryCode;
            $country->save();
            return $country;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to create country '{$countryName}'. Error: " . $e->getMessage(),
                500,
                "Country Creation Failed 🛑",
                "A system error occurred while trying to save the new country record. Please try again or contact support.",
                null
            );
        }
    }
    public function updateCountry(array $data, string $countryId)
    {
        try {
            $country = Country::find($countryId);

            if (!$country) {
                throw new AppException(
                    "Country ID '{$countryId}' not found.",
                    404,
                    "Country Not Found 🔎",
                    "The country record you are trying to update could not be found.",
                    null
                );
            }

            $filteredData = array_filter($data);

            if (isset($filteredData['country']) && Country::where('country', $filteredData['country'])->where('id', '!=', $countryId)->exists()) {
                throw new AppException(
                    "A country with the name '{$filteredData['country']}' already exists.",
                    409,
                    "Duplicate Country Name 🌍",
                    "The country name you entered is already in use by another record.",
                    null
                );
            }
            if (isset($filteredData['code']) && Country::where('code', $filteredData['code'])->where('id', '!=', $countryId)->exists()) {
                throw new AppException(
                    "A country with the code '{$filteredData['code']}' already exists.",
                    409,
                    "Duplicate Country Code 🏷️",
                    "The country code you entered is already in use by another record.",
                    null
                );
            }

            $country->update($filteredData);
            return $country;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to update country ID '{$countryId}'. Error: " . $e->getMessage(),
                500,
                "Country Update Failed 🛑",
                "A system error occurred while trying to save the changes. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkUpdateCountry(array $updateCountryList)
    {
        if (empty($updateCountryList)) {
            throw new AppException(
                "No country data provided for bulk update.",
                400,
                "No Data Provided 📝",
                "Please provide a list of country records to update.",
                null
            );
        }

        $successfulUpdates = [];
        $failedUpdates = [];
        DB::beginTransaction();

        try {
            foreach ($updateCountryList as $updateCountry) {
                $countryId = $updateCountry['country_id'];
                $filteredData = array_filter($updateCountry);

                try {
                    $country = Country::findOrFail($countryId);

                    if (isset($filteredData['country']) && Country::where('country', $filteredData['country'])->where('id', '!=', $countryId)->exists()) {
                        throw new Exception("Duplicate country name: '{$filteredData['country']}'", 409);
                    }
                    if (isset($filteredData['code']) && Country::where('code', $filteredData['code'])->where('id', '!=', $countryId)->exists()) {
                        throw new Exception("Duplicate country code: '{$filteredData['code']}'", 409);
                    }

                    $country->update($filteredData);
                    $successfulUpdates[] = $country;
                } catch (ModelNotFoundException $e) {
                    $failedUpdates[] = ['id' => $countryId, 'reason' => "Country not found."];
                } catch (Exception $e) {
                    $failedUpdates[] = ['id' => $countryId, 'reason' => $e->getMessage()];
                }
            }

            if (!empty($failedUpdates)) {
                DB::rollBack();
                $failedList = collect($failedUpdates)->map(fn($f) => "ID {$f['id']}: {$f['reason']}")->implode('; ');
                throw new AppException(
                    "Bulk update failed for some records. Details: {$failedList}",
                    400,
                    "Bulk Update Failed 🛑",
                    "The entire batch update was rolled back because of errors (missing records or duplicates). Details: {$failedList}",
                    null
                );
            }

            DB::commit();
            return $successfulUpdates;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "A critical system error occurred during bulk update: " . $e->getMessage(),
                500,
                "System Update Failed 🚨",
                "We were unable to complete the bulk update. Please try again or contact support.",
                null
            );
        }
    }
    public function deleteCountry(string $countryId)
    {
        try {
            $country = Country::find($countryId);
            $countryName = $country->country ?? 'Unknown';

            if (!$country) {
                throw new AppException(
                    "Country ID '{$countryId}' not found for deletion.",
                    404,
                    "Country Not Found 🗑️",
                    "The country record you are trying to delete could not be found.",
                    null
                );
            }

            $country->delete();
            return $country;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            $message = "Failed to delete country '{$countryName}' (ID: {$countryId}). Error: " . $e->getMessage();

            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                throw new AppException(
                    $message,
                    409,
                    "Deletion Blocked 🔒",
                    "Cannot delete the country because it is linked to other records (e.g., branches or regions). Please remove all associations first.",
                    null
                );
            }

            throw new AppException(
                $message,
                500,
                "Deletion Failed 🛑",
                "A system error occurred during deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function bulkDeleteCountry($countryIds)
    {
        if (empty($countryIds)) {
            throw new AppException(
                "No country IDs provided for bulk deletion.",
                400,
                "No IDs Provided 📝",
                "Please provide a list of country IDs to delete.",
                null
            );
        }

        $deletedCountries = [];
        $failedDeletions = [];
        DB::beginTransaction();

        try {
            foreach ($countryIds as $item) {
                $countryId = $item['country_id'];
                $countryName = 'Unknown';

                try {
                    $country = Country::findOrFail($countryId);
                    $countryName = $country->country;

                    $country->delete();
                    $deletedCountries[] = $country;
                } catch (ModelNotFoundException $e) {
                    $failedDeletions[] = ['id' => $countryId, 'reason' => "Country not found."];
                } catch (Throwable $e) {
                    $reason = str_contains($e->getMessage(), 'Integrity constraint violation')
                        ? "Linked to other records (e.g., regions/branches)."
                        : "System error during deletion.";
                    $failedDeletions[] = ['id' => $countryId, 'reason' => $reason];
                }
            }

            if (!empty($failedDeletions)) {
                DB::rollBack();
                $failedList = collect($failedDeletions)->map(fn($f) => "ID {$f['id']}: {$f['reason']}")->implode('; ');
                throw new AppException(
                    "Bulk deletion failed for some records. Details: {$failedList}",
                    409,
                    "Bulk Deletion Failed 🛑",
                    "The entire batch deletion was rolled back because of errors (missing records or existing associations). Details: {$failedList}",
                    null
                );
            }

            DB::commit();
            return $deletedCountries;
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Throwable $e) {
            DB::rollBack();
            throw new AppException(
                "A critical system error occurred during bulk deletion: " . $e->getMessage(),
                500,
                "System Deletion Failed 🚨",
                "We were unable to complete the bulk deletion. Please try again or contact support.",
                null
            );
        }
    }
    public function getCountries()
    {
        try {
            $countries = Country::where("status", true)->get();

            if ($countries->isEmpty()) {
                throw new AppException(
                    "No active country records found in the database.",
                    404,
                    "No Countries Found 🌍",
                    "There are currently no active country records in the system. Please add a new country to populate the list.",
                    null
                );
            }
            return $countries;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve country list. Error: " . $e->getMessage(),
                500,
                "Data Retrieval Failed 🛑",
                "A system error occurred while trying to load the countries. Please try again or contact support.",
                null
            );
        }
    }
}
