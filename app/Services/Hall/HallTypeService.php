<?php

namespace App\Services\Hall;

use App\Exceptions\AppException;
use App\Models\HallType;

class HallTypeService
{
    public function createHallType($data)
    {
        $existingType = HallType::Where("name", $data["name"])
            ->first();
        if ($existingType) {
            throw new AppException(
                "A hall type with this name already exists",
                409,
                "Duplicate Hall Type",
                "{$data['name']} already exists. Please try another name."
            );
        }

        $hallType = HallType::create([
            "name" => $data["name"],
            "description" => $data["description"] ?? null,
            "key" => $data["key"],
            "background_color" => $data["background_color"],
            "text_color" => $data["text_color"]
        ]);

        return $hallType;
    }

    public function updateHallType(array $data, $id)
    {
        $hallType = HallType::find($id);

        if (!$hallType) {
            throw new AppException(
                "Hall type not found",
                404,
                "Not Found",
                "The hall type with ID {$id} does not exist."
            );
        }

        $existingType = HallType::where("name", $data["name"])
            ->where("id", "!=", $hallType->id)
            ->first();

        if ($existingType) {
            throw new AppException(
                "A hall type with this name already exists",
                409,
                "Duplicate Hall Type",
                "The name '{$data['name']}' is already used by another hall type."
            );
        }

        $hallType->update([
            "name"            => $data["name"],
            "description"     => $data["description"] ?? null,
            "key"             => $data["key"] ?? $hallType->key,
            "background_color" => $data["background_color"] ?? $hallType->background_color,
            "text_color"      => $data["text_color"] ?? $hallType->text_color,
        ]);

        return $hallType->fresh();
    }

    public function deactivateHallType($hallTypeId)
    {
        $hallType = HallType::find($hallTypeId);
        if (!$hallType) {
            throw new AppException(
                "Hall Type Not Found",
                400,
                "Hall Type Not Found",
                "Hall Type Not Found Please ensure that the hall type has not been deleted and try again"
            );
        }

        if ($hallType->status == "inactive") {
            throw new AppException(
                "Hall Type Already Deactivated",
                409,
                "Hall Type Already Deactivated",
                "Hall Type Has Already been deactivated you can now only activate the hall"
            );
        }

        $hallType->status = "inactive";
        $hallType->save();
        return $hallType;
    }

    public function activateHallType($hallTypeId)
    {
        $hallType = HallType::find($hallTypeId);
        if (!$hallType) {
            throw new AppException(
                "Hall Type Not Found",
                400,
                "Hall Type Not Found",
                "Hall Type Not Found Please ensure that the hall type has not been deleted and try again"
            );
        }

        if ($hallType->status == "active") {
            throw new AppException(
                "Hall Type Already Activated",
                409,
                "Hall Type Already Activated",
                "Hall Type Has Already been Activated you can now only deactivate the hall"
            );
        }

        $hallType->status = "active";
        $hallType->save();
        return $hallType;
    }

    public function getAllHallTypes()
    {
        $hallTypes = HallType::all();
        return $hallTypes;
    }

    public function getActiveHallTypes()
    {
        $activeHallTypes = HallType::where("status", "active")->get();
        return $activeHallTypes;
    }

    public function deleteHallType($hallTypeId)
    {
        $hallType = HallType::find($hallTypeId);
        if (!$hallType) {
            throw new AppException(
                "Hall Type Not Found",
                400,
                "Hall Type Not Found",
                "Hall Type Not Found Please ensure that the hall type has not been deleted and try again"
            );
        }

        $hallType->delete();
         return $hallType;
    }
}
