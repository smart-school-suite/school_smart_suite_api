<?php

namespace App\Services\Gender;

use App\Exceptions\AppException;
use App\Models\Gender;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GenderService
{
    public function createGender($data)
    {
        $existingGender = Gender::where('name', $data['name'])->first();

        if ($existingGender) {
            throw new AppException(
                "A gender with the same name already exists",
                409,
                "Duplicate Gender",
                "{$data['name']} already exists. Please try another name."
            );
        }

        return Gender::create($data);
    }

    public function getAllGender()
    {
        return Gender::all();
    }

    public function getActiveGender()
    {
        return Gender::where('status', 'active')->get();
    }

    public function getGenderById($genderId)
    {
        $gender = Gender::find($genderId);

        if (!$gender) {
            throw new ModelNotFoundException("Gender with ID {$genderId} not found.");
        }

        return $gender;
    }

    public function updateGender($updateData, $genderId)
    {
        $gender = Gender::find($genderId);

        if (!$gender) {
            throw new ModelNotFoundException("Gender with ID {$genderId} not found.");
        }

        $cleanData = array_filter($updateData, function ($value) {
            return $value !== '' && $value !== null;
        });

        if (isset($cleanData['name']) && $cleanData['name'] !== $gender->name) {
            $existing = Gender::where('name', $cleanData['name'])
                ->where('id', '!=', $genderId)
                ->first();

            if ($existing) {
                throw new AppException(
                    "A gender with the name '{$cleanData['name']}' already exists",
                    409,
                    "Duplicate Gender Name",
                    "Please choose a different name."
                );
            }
        }

        $gender->update($cleanData);

        return $gender->fresh();
    }

    public function deactivateGender($genderId)
    {
        $gender = Gender::find($genderId);

        if (!$gender) {
            throw new ModelNotFoundException("Gender with ID {$genderId} not found.");
        }

        if ($gender->status === 'inactive') {
            throw new AppException(
                "Gender is already inactive",
                400,
                "Already Inactive",
                "This gender has already been deactivated."
            );
        }

        $gender->update(['status' => 'inactive']);

        return $gender;
    }

    public function activateGender($genderId)
    {
        $gender = Gender::find($genderId);

        if (!$gender) {
            throw new ModelNotFoundException("Gender with ID {$genderId} not found.");
        }

        if ($gender->status === 'active') {
            throw new AppException(
                "Gender is already active",
                400,
                "Already Active",
                "This gender is already active."
            );
        }

        $gender->update(['status' => 'active']);

        return $gender;
    }

    public function deleteGender($genderId)
    {
        $gender = Gender::find($genderId);

        if (!$gender) {
            throw new ModelNotFoundException("Gender with ID {$genderId} not found.");
        }

        $gender->delete();

        return true;
    }
}
