<?php

namespace App\Services;
use App\Models\Specialty;
use Exception;
use Illuminate\Support\Facades\DB;

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
        $specialty->description = $data["description"] ?? null;
        $specialty->level_id = $data["level_id"];
        $specialty->save();
        return $specialty;
    }

    public function updateSpecialty(array $data, $currentSchool, $specialtyId){
        $specailty = Specialty::where("school_branch_id", $currentSchool->id)->find($specialtyId);
        if(!$specailty){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $specailty->update( $filterData );
        return $specailty;
     }

     public function deleteSpecailty($currentSchool, $specialtyId){
        $specailty = Specialty::where("school_branch_id", $currentSchool->id)->find($specialtyId);
        if(!$specailty){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        $specailty->delete();
        return $specailty;
     }

     public function getSpecailties($currentSchool){
        $specailtyData = Specialty::where("school_branch_id", $currentSchool->id)->with(['level', 'hos.hosable'])->get();
        return $specailtyData;
     }

     public function getSpecailtyDetails($currentSchool, $specialtyId){
        $specailty = Specialty::where("school_branch_id", $currentSchool->id)->with(['level', 'department', 'hos.hosable'])->find($specialtyId);
        if(!$specailty){
            return ApiResponseService::error("Specailty Not Found", null, 404);
        }
        return $specailty;
     }

     public function deactivateSpecialty($specialtyId){
        $specialty = Specialty::findOrFail($specialtyId);
        $specialty->status = "inactive";
        $specialty->save();
        return $specialty;
     }

     public function activateSpecialty($specialtyId){
        $specialty = Specialty::findOrFail($specialtyId);
        $specialty->status = "active";
        $specialty->save();
        return $specialty;
     }

     public function bulkUpdateSpecialty($updateDataList){
          $result = [];
          try{
              DB::beginTransaction();
             foreach($updateDataList as $updateData){
                $specialty = Specialty::findOrFail($updateData['id']);
                if ($specialty) {
                    $cleanedData = array_filter($updateData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $specialty->update($cleanedData);
                    }
                }
                $result[] = $specialty;
             }
             DB::commit();
            return $result;
          }
          catch(Exception $e){
            DB::rollBack();
            throw $e;

          }
     }

     public function bulkDeactivateSpecialty($specialtyIds){
           $result = [];
          try{
            DB::beginTransaction();
            foreach($specialtyIds as $specialtyId){
               $specialty = Specialty::findOrFail($specialtyId['specialty_id']);
               $specialty->status = "inactive";
               $specialty->save();
               $result[] = [
                  $specialty
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

     public function bulkActivateSpecialty(array $specialtyIds){
        $result = [];
        try{
          DB::beginTransaction();
          foreach($specialtyIds as $specialtyId){
             $specialty = Specialty::findOrFail($specialtyId['specialty_id']);
             $specialty->status = "active";
             $specialty->save();
             $result[] = [
                $specialty
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

     public function bulkDeleteSpecialty($specialtyIds){
        $result = [];
        try{
           DB::beginTransaction();
            foreach($specialtyIds as $specialtyId){
             $specialty = Specialty::findOrFail($specialtyId['specialty_id']);
             $specialty->delete();
             $result[] = [
                $specialty
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

