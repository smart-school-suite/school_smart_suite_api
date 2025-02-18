<?php

namespace App\Services;
use App\Models\HOS;
use App\Models\Schooladmin;
use App\Models\Teacher;
class HosService
{
    // Implement your logic here
    public function assignHeadOfSpecialty($hodData, $currentSchool)
    {
        $hosable = Teacher::find($hodData["hosable_id"]);
        $hosableType = null;

        if ($hosable) {
            $hosableType = 'App\\Models\\Teacher';
        } else {
            $hosable = SchoolAdmin::find($hodData["hosable_id"]);
            if ($hosable) {
                $hosableType = 'App\\Models\\SchoolAdmin';
            }
        }

        if (!$hosableType) {
            return ApiResponseService::error("The provided Credentials Are Incorrect", null, 404);
        }

        HOS::where("school_branch_id", $currentSchool->id)->where('specialty_id', $hodData["specialty_id"])->delete();

        $assigedHod = HOS::create([
            'specialty_id' => $hodData["specialty_id"],
            'hosable_id' => $hodData["hosable_id"],
            'school_branch_id' => $currentSchool->id,
            'hodable_type' => $hosableType,
        ]);

        return $assigedHod;
    }

    public function removeHos($hodId, $currentSchool){
        $findHod = HOS::where("school_branch_id", $currentSchool->id)->find($hodId);
        if(!$findHod){
            return ApiResponseService::error("HOD not found", null, 404);
        }
        $findHod->delete();
        return $findHod;
    }

    public function getAssignedHos($currentSchool){
        $getHods = HOS::where("school_branch_id", $currentSchool->id)->with(['hosable', 'specialty'])->get();
        return $getHods;
    }
}
