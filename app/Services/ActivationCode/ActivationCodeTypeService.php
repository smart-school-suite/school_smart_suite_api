<?php

namespace App\Services\ActivationCode;

use App\Exceptions\AppException;
use App\Models\ActivationCodeType;

class ActivationCodeTypeService
{
    public function createActivationCodeType($data)
    {
        $existingCodeType = ActivationCodeType::where("name", $data['name'])
            ->where("country_id", $data['country_id'])
            ->first();
        if ($existingCodeType) {
            throw new AppException(
                "Existing Code Type",
                404,
                "Code Type Conflict",
                "{$existingCodeType->name}, already exists please try another name"
            );
        }

        $createCodeType = ActivationCodeType::create([
            'name' => $data["name"],
            'price' => $data["price"],
            'status' => "active",
            'description' => $data["description"],
            'type' => $data["type"],
            'country_id' => $data["country_id"]
        ]);

        return $createCodeType;
    }
    public function updateActivationCodeType($data, $activationCodeTypeId)
    {
        $activationCodeType = ActivationCodeType::find($activationCodeTypeId);

        if (!$activationCodeType) {
            throw new AppException(
                "Missing Activation Code Type",
                404,
                "Missing Code Type",
                "Missing Activation Code Type. Please ensure that it has not been deleted and try again."
            );
        }

        $query = ActivationCodeType::where('name', $data['name'] ?? $activationCodeType->name)
            ->where('country_id', $data['country_id'] ?? $activationCodeType->country_id)
            ->where('id', '!=', $activationCodeTypeId);

        if ($query->exists()) {
            throw new AppException(
                "Existing Code Type",
                409,
                "Code Type Conflict",
                "An activation code type with this name already exists in the selected country."
            );
        }

        $filteredData = array_filter($data, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($filteredData)) {
            throw new AppException(
                "No Changes",
                400,
                "No Updates Provided",
                "No valid changes were provided for the update."
            );
        }

        if (isset($filteredData['status'])) {
            $filteredData['status'] = strtolower($filteredData['status']) === 'active' ? 'active' : 'inactive';
        }

        $activationCodeType->update($filteredData);

        return $activationCodeType;
    }
    public function deleteActivationCodeType($activationCodeTypeId)
    {
        $activationCodeType = ActivationCodeType::find($activationCodeTypeId);

        if (!$activationCodeType) {
            throw new AppException(
                "Missing Activation Code Type",
                404,
                "Missing Code Type",
                "Missing Activation Code Type. Please ensure that it has not been deleted and try again."
            );
        }

        $activationCodeType->delete();
        return $activationCodeType;
    }
    public function getAllActivationCodeTypes()
    {
        $activationCodeTypes = ActivationCodeType::with(['country'])->get();
        return $activationCodeTypes;
    }
    public function activationActivationCodeType($activationCodeTypeId)
    {
        $activationCodeType = ActivationCodeType::find($activationCodeTypeId);

        if (!$activationCodeType) {
            throw new AppException(
                "Missing Activation Code Type",
                404,
                "Missing Code Type",
                "Missing Activation Code Type. Please ensure that it has not been deleted and try again."
            );
        }

        if ($activationCodeType->status == "active") {
            throw new AppException(
                "Activation Conflict",
                409,
                "Activation Conflict",
                "{$activationCodeType->name}, already activated"
            );
        }

        $activationCodeType->status = "active";
        $activationCodeType->save();

        return $activationCodeType;
    }
    public function deactivateActivationCodeType($activationCodeTypeId)
    {
        $activationCodeType = ActivationCodeType::find($activationCodeTypeId);

        if (!$activationCodeType) {
            throw new AppException(
                "Missing Activation Code Type",
                404,
                "Missing Code Type",
                "Missing Activation Code Type. Please ensure that it has not been deleted and try again."
            );
        }

        if ($activationCodeType->status == "inactive") {
            throw new AppException(
                "Activation Conflict",
                409,
                "Activation Conflict",
                "{$activationCodeType->name}, already Deactivated"
            );
        }

        $activationCodeType->status = "inactive";
        $activationCodeType->save();

        return $activationCodeType;
    }
    public function getActivationCodeTypeCountryId($currentSchool)
    {
        $activationCodeTypes = ActivationCodeType::where("country_id", $currentSchool->school->country_id)
            ->where("status", "active")
            ->with(['country'])->get();
        return $activationCodeTypes;
    }
    public function getActivationCodeTypeDetail($activationCodeTypeId)
    {
        $activationCodeType = ActivationCodeType::with(['country'])
            ->find($activationCodeTypeId);

        if (!$activationCodeType) {
            throw new AppException(
                "Missing Activation Code Type",
                404,
                "Missing Code Type",
                "Missing Activation Code Type. Please ensure that it has not been deleted and try again."
            );
        }

        return $activationCodeType;
    }
}
