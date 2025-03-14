<?php

namespace App\Services;
use App\Models\Specialty;
class SpecailtyService
{
    // Implement your logic here

    public function createSpecialty(array $data, $currentSchool){
        $specialty = new Specialty();
        $specialty->school_branch_id = $currentSchool->id;
        $specialty->department_id = $data["department_id"];
        $specialty->specialty_name = $data["specialty_name"];
        $specialty->registration_fee = $data["registration_fee"];
        $specialty->school_fee = $data["school_fee"];
        $specialty->level_id = $data["level_id"];
        $specialty->save();
        return $specialty;
    }

    public function updateSpecialty(array $data, $currentSchool, $specialtyId){
        $specailtyExists = Specialty::where("school_branch", $currentSchool->id)->find($specialtyId);
        if($specailtyExists){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $specailtyExists->update( $filterData );
        return $specailtyExists;
     }

     public function deleteSpecailty($currentSchool, $specialtyId){
        $specailtyExists = Specialty::where("school_branch", $currentSchool->id)->find($specialtyId);
        if($specailtyExists){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        $specailtyExists->delete();
        return $specailtyExists;
     }

     public function getSpecailties($currentSchool){
        $specailtyData = Specialty::where("school_branch_id", $currentSchool->id)->with('level')->get();
        return $specailtyData;
     }

     public function getSpecailtyDetails($currentSchool, $specialtyId){
        $specailtyExists = Specialty::where("school_branch_id", $currentSchool->id)->with(['level', 'department'])->find($specialtyId);
        if(!$specailtyExists){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        return $specailtyExists;
     }
}

