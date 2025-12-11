<?php

namespace App\Services\SubscriptionPlan;

use App\Exceptions\AppException;
use App\Models\Feature;

class FeatureService
{
    public function createFeature($data)
    {
        $existingFeature = Feature::where("name", $data["name"])
            ->where("country_id", $data['country_id'])->first();
        if ($existingFeature) {
            throw new AppException(
                "Feature With This Name Already Exists",
                409,
                "Duplicate Feature",
                "Feature with this name {$data['name']} already exists please change the name to a new name and try again"
            );
        }

      $feature =  Feature::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'key' => $data['key'],
            'country_id' => $data['country_id']
        ]);

        return $feature;
    }

    public function updateFeature($data, $featureId)
    {
        $feature = Feature::find($featureId);
        if (!$feature) {
            throw new AppException(
                "Feature Not Found",
                404,
                "Resource Not Found",
                "The feature with ID {$featureId} does not exist."
            );
        }

        $updateData = [];
        foreach ($data as $key => $value) {
            if (!is_null($value) && $value !== '' && isset($feature->$key) && $feature->$key !== $value) {
                $updateData[$key] = $value;
            }
        }

        if (empty($updateData)) {
            throw new AppException(
                "No Valid Data For Update",
                400,
                "No Change Detected",
                "The provided data does not contain any valid new information to update Feature ID {$featureId}."
            );
        }

        if (isset($updateData['name']) || isset($updateData['country_id'])) {
            $nameToCheck = $updateData['name'] ?? $feature->name;
            $countryIdToCheck = $updateData['country_id'] ?? $feature->country_id;

            $duplicateFeature = Feature::where('name', $nameToCheck)
                ->where('country_id', $countryIdToCheck)
                ->where('id', '!=', $featureId)
                ->first();

            if ($duplicateFeature) {
                throw new AppException(
                    "Duplicate Feature Name and Country Combination",
                    409,
                    "Duplicate Feature",
                    "A feature with the name '{$nameToCheck}' already exists for country ID '{$countryIdToCheck}'."
                );
            }
        }

        $feature->update($updateData);
        return $feature;
    }
    public function deleteFeature($featureId)
    {
        $feature = Feature::find($featureId);
        if (!$feature) {
            throw new AppException(
                "Feature Not Found",
                404,
                "Feature Not Found",
                "Feature not found it looks like the feature must have been accidentally deleted please verify and try again"
            );
        }

        $feature->delete();
        return $feature;
    }

    public function deactivateFeature($featureId)
    {
        $feature = Feature::find($featureId);
        if (!$feature) {
            throw new AppException(
                "Feature Not Found",
                404,
                "Feature Not Found",
                "Feature not found it looks like the feature must have been accidentally deleted please verify and try again"
            );
        }

        if ($feature->status == "inactive") {
            throw new AppException(
                "Feature Already Deactivated",
                400,
                "Feature Already Deactivated",
                "{$feature->name} already deactivated"
            );
        }

        $feature->update([
            'status' => "inactive"
        ]);

        return $feature;
    }

    public function activateFeature($featureId)
    {
        $feature = Feature::find($featureId);
        if (!$feature) {
            throw new AppException(
                "Feature Not Found",
                404,
                "Feature Not Found",
                "Feature not found it looks like the feature must have been accidentally deleted please verify and try again"
            );
        }

        if ($feature->status == "active") {
            throw new AppException(
                "Feature Already Activated",
                400,
                "Feature Already Activated",
                "{$feature->name} already Activated"
            );
        }

        $feature->update([
            'status' => "inactive"
        ]);

        return $feature;
    }

    public function getFeatures(){
        return Feature::with(['country'])->all();
    }
}
