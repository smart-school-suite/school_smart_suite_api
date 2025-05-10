<?php

namespace App\Services;

use App\Models\Parents;
use Exception;
use Illuminate\Support\Facades\DB;

class ParentService
{
    // Implement your logic here

    public function createParent($parentData, $currentSchool)
    {
        $parent = Parents::create([
            'school_branch_id' => $currentSchool->id,
            'name' => $parentData['name'],
            'address' => $parentData['address'],
            'email' => $parentData['email'],
            "phone_one" => $parentData['phone_one'],
            "phone_two" => $parentData['phone_two'],
            "relationship_to_student" => $parentData['relationship_to_student'],
            "preferred_language" => $parentData['preferred_language']
        ]);
        return $parent;
    }
    public function getAllParents($currentSchool)
    {
        $parents = Parents::Where('school_branch_id', $currentSchool->id)->with('student')->get();
        return $parents;
    }

    public function deleteParent($parentId, $currentSchool)
    {
        $parentExist = Parents::where("school_branch_id", $currentSchool->id)->find($parentId);
        if (!$parentExist) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $parentExist->delete();
    }

    public function bulkDeleteParent($parentIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($parentIds as $parentId) {
                $parent = Parents::findOrFail($parentId);
                $parent->delete();
                $result[] = [
                    $parent
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateParent(array $data, $parentId, $currentSchool)
    {
        $parentExist = Parents::where("school_branch_id", $currentSchool->id)->find($parentId);
        if (!$parentExist) {
            return ApiResponseService::error("Parent Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $parentExist->update($filterData);
        return $parentExist;
    }

    public function bulkUpdateParent(array $updateDataArray)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataArray as $updateData) {
                $parent = Parents::findOrFail($updateData['student_id']);
                $filterData = array_filter($updateData);
                $parent->update($filterData);
                $result[] = [
                    $parent
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getParentDetails($parentId, $currentSchool)
    {
        $parentDetails = Parents::where("school_branch_id", $currentSchool->id)
            ->where("id", $parentId)
            ->with(['student.specialty', 'student.level'])
            ->get();
        if (!$parentDetails) {
            return ApiResponseService::error("Parent not found", null, 404);
        }
        return $parentDetails;
    }
}
