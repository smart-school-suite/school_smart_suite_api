<?php

namespace App\Services;

use App\Models\Educationlevels;

class EducationLevelService
{
    // Implement your logic here

    public function createEducationLevel(array $data)
    {
        $educationLevel = new Educationlevels();
        $educationLevel->name = $data["name"];
        $educationLevel->level = $data["level"];

        $educationLevel->save();
        return $educationLevel;
    }

    public function updateEducationLevel(array $data, $levelId)
    {
        $educationLevel = Educationlevels::find($levelId);
        if (!$educationLevel) {
            return ApiResponseService::error("Education level not found", null, 404);
        }

        $filteredData = array_filter($data);
        $educationLevel->update($filteredData);

        return $educationLevel;
    }

    public function deleteEducationLevel($levelId)
    {
        $educationLevel = Educationlevels::find($levelId);
        if (!$educationLevel) {
            return ApiResponseService::error("Education Level not found", null, 404);
        }
        $educationLevel->delete();

        return $educationLevel;
    }

    public function getEducationLevels()
    {
        $educationLevels = Educationlevels::all();
        return $educationLevels;
    }
}
