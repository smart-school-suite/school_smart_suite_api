<?php

namespace App\Services;
use App\Models\HOS;
use App\Models\Schooladmin;
use Exception;
use Illuminate\Support\Facades\DB;
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
            'hosable_type' => $hosableType,
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

    public function getAllHOS($currentSchool) {
         $getAllHos = HOS::where("school_branch_id", $currentSchool->id)->with(['hosable', 'specialty', 'specialty.level'])->get();
         return $getAllHos;
    }

    public function getHosDetails($hosId){
        $hosDetails = HOS::with(['hosable', 'specialty', 'specialty.level'])->findOrFail($hosId);
        return $hosDetails;
    }

    public function bulkRemoveHos($hosIds){
        $result = [];
        try{
           DB::beginTransaction();
           foreach($hosIds as $hosId){
              $hos = HOS::findOrFail($hosId['id']);
              $hos->delete();
              $result[] = [
                 $hos
              ];
           }
           DB::commit();
           return $result;
        }
        catch(Exception $e){
           DB::rollBack();
           throw $e;
        }
    }

}
