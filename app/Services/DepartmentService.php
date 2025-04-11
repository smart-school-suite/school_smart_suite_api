<?php

namespace App\Services;
use Exception;
use illuminate\Support\Facades\DB;
use App\Models\Department;

class DepartmentService
{
    // Implement your logic here
    public function createDepartment(array $data, $currentSchool)
    {
        $department = new Department();
        $department->department_name = $data["department_name"];
        $department->description = $data["description"];
        $department->school_branch_id = $currentSchool->id;
        $department->save();
        return $department;
    }

    public function updateDepartment(string $department_id, array $data, $currentSchool)
    {
        $department = Department::find($department_id);
        if (!$department) {
            return ApiResponseService::error("Department Not found", null, 404);
        }
        $filterData = array_filter($data);
        $department->update($filterData);
        return $department;
    }

    public function deleteDepartment(string $department_id)
    {
        $department = Department::find($department_id);
        if (!$department) {
            return ApiResponseService::error("Department Not Found", null, 404);
        }
        $department->delete();

        return $department;
    }

    public function getDepartments($currentSchool)
    {
        $departmentData = Department::where("school_branch_id", $currentSchool->id)
            ->with(['hods.hodable'])
            ->get();
        return $departmentData;
    }

    public function getDepartmentDetails($currentSchool, $department_id)
    {
        $findDeparment = Department::where("school_branch_id", $currentSchool->id)
            ->with(['hods.hodable'])
            ->find($department_id);
        if (!$findDeparment) {
            return ApiResponseService::error("Department not found", null, 404);
        }
        return $findDeparment;
    }

    public function deactivateDepartment(string $departmentId){
        $department = Department::findOrFail($departmentId);
        $department->status = 'inactive';
        $department->save();
        return $department;
    }

    public function activateDepartment(string $departmentId){
        $department = Department::findOrFail($departmentId);
        $department->status = "active";
        $department->save();
        return $department;
    }

    public function bulkDeactivateDepartment(array $departmentIds){
        $result = [];
        foreach($departmentIds as $departmentId){
             $department = Department::findOrFail($departmentId['id']);
             $department->status = 'inactive';
             $department->save();
             $result[] = [
                 'department_name' => $department->department_name
             ];
        }
        return $result;
    }

    public function bulkActivateDepartment(array $departmentIds){
        $result = [];
        foreach($departmentIds as $departmentId){
             $department = Department::findOrFail($departmentId['id']);
             $department->status = 'active';
             $department->save();
             $result[] = [
                 'department_name' => $department->department_name
             ];
        }
        return $result;
    }

    public function bulkUpdateDepartment(array $updateDataList){
        $result = [];
        try{
            DB::beginTransaction();
            foreach($updateDataList as $updateData){
                $department = Department::findOrFail($updateData['id']);
                if ($department) {
                    $cleanedData = array_filter($updateData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $department->update($cleanedData);
                    }
                }
                $result[] = [
                   $department
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

    public function bulkDeleteDepartment(array $departmentIds){
         $result = [];
         DB::beginTransaction();
          try{
            foreach($departmentIds as $departmentId){
                $department = Department::findOrFail($departmentId);
                $department->delete();
                $result[] = [
                    $department
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
