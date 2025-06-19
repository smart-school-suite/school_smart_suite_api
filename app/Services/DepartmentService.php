<?php

namespace App\Services;
use App\Jobs\StatisticalJobs\OperationalJobs\DepartmentStatJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use Illuminate\Support\Str;


class DepartmentService
{
    // Implement your logic here
    public function createDepartment(array $data, $currentSchool)
    {
        $department = new Department();
        $departmentId = Str::uuid();
        $department->id = $departmentId;
        $department->department_name = $data["department_name"];
        $department->description = $data["description"];
        $department->school_branch_id = $currentSchool->id;
        $department->save();
        DepartmentStatJob::dispatch($departmentId, $currentSchool->id);
        return $department;
    }

    public function updateDepartment(string $departmentId, array $data, $currentSchool)
    {
        $department = Department::where("school_branch_id", $currentSchool->id)->find($departmentId);
        if (!$department) {
            return ApiResponseService::error("Department Not found", null, 404);
        }
        $filterData = array_filter($data);
        $department->update($filterData);
        return $department;
    }

    public function deleteDepartment(string $departmentId)
    {
        $department = Department::find($departmentId);
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

    public function getDepartmentDetails($currentSchool, $departmentId)
    {
        $findDeparment = Department::where("school_branch_id", $currentSchool->id)
            ->with(['hods.hodable'])
            ->find($departmentId);
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
             $department = Department::findOrFail($departmentId['department_id']);
             $department->status = 'inactive';
             $department->save();
             $result[] = $department;
        }
        return $result;
    }

    public function bulkActivateDepartment(array $departmentIds){
        $result = [];
        foreach($departmentIds as $departmentId){
             $department = Department::findOrFail($departmentId['department_id']);
             $department->status = 'active';
             $department->save();
             $result[] = $department;
        }
        return $result;
    }

   public function bulkUpdateDepartment(array $updateDataList): array
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataList as $updateData) {
                $department = Department::findOrFail($updateData['department_id']);
                if ($department) {
                    $cleanedData = array_filter($updateData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $department->update($cleanedData);
                    }
                    $result[] = $department;
                }
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeleteDepartment(array $departmentIds){
         $result = [];
         DB::beginTransaction();
          try{
            foreach($departmentIds as $departmentId){
                $department = Department::findOrFail($departmentId['department_id']);
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
