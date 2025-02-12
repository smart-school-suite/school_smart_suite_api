<?php

namespace App\Services;

use App\Models\Parents;

class ParentService
{
    // Implement your logic here
    public function getAllParentNoRelation($currentSchool){
        $parents = Parents::where("school_branch_id", $currentSchool->id)->get();
        return $parents;
    }
    public function getAllParents($currentSchool) {
        $parents = Parents::Where('school_branch_id', $currentSchool->id)->with('student')->get();
        return $parents;
    }

    public function deleteParent($parentId, $currentSchool){
        $parentExist = Parents::where("school_branch_id", $currentSchool->id)->find($parentId);
        if(!$parentExist){
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $parentExist->delete();
    }

    public function updateParent(array $data, $parentId, $currentSchool){
        $parentExist = Parents::where("school_branch_id")->find($parentId);
        if(!$parentExist){
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $parentExist->update($filterData);
        return $parentExist;
    }

    public function getParentDetails($parentId, $currentSchool){
        $parentDetails = Parents::where("school_branch_id", $currentSchool->id)
         ->with(['student.specialty', 'student.level'])
         ->get();
        if(!$parentDetails){
            return ApiResponseService::error("Parent not found", null,404);
        }
        return $parentDetails;
    }

}
