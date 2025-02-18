<?php

namespace App\Services;

use App\Models\HOD;
use App\Models\Teacher;
use App\Models\Schooladmin;

class HodService
{
    // Implement your logic here
    public function assignHeadOfDepartment($hodData, $currentSchool)
    {
        $hodable = Teacher::find($hodData["hodable_id"]);
        $hodableType = null;

        if ($hodable) {
            $hodableType = 'App\\Models\\Teacher';
        } else {
            $hodable = SchoolAdmin::find($hodData["hodable_id"]);
            if ($hodable) {
                $hodableType = 'App\\Models\\SchoolAdmin';
            }
        }

        if (!$hodableType) {
            return ApiResponseService::error("The provided Credentials Are Incorrect", null, 404);
        }

        HOD::where("school_branch_id", $currentSchool->id)->where('department_id', $hodData["department_id"])->delete();

        $assigedHod = HOD::create([
            'department_id' => $hodData["department_id"],
            'hodable_id' => $hodData["hodable_id"],
            'school_branch_id' => $currentSchool->id,
            'hodable_type' => $hodableType,
        ]);

        return $assigedHod;
    }

    public function removeHod($hodId, $currentSchool){
        $findHod = HOD::where("school_branch_id", $currentSchool->id)->find($hodId);
        if(!$findHod){
            return ApiResponseService::error("HOD not found", null, 404);
        }
        $findHod->delete();
        return $findHod;
    }

    public function getAssignedHods($currentSchool){
        $getHods = HOD::where("school_branch_id", $currentSchool->id)->with(['hodable', 'department'])->get();
        return $getHods;
    }
}
