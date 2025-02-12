<?php

namespace App\Services;

use App\Models\Department;
use Spatie\FlareClient\Api;

class DepartmentService
{
    // Implement your logic here
    public function createDepartment(array $data, $currentSchool)
    {
        $department = new Department();
        $department->name = $data["department_name"];
        $department->HOD = $data["HOD"];
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
        $departmentData = Department::where("school_branch_id", $currentSchool->id)->get();
        return $departmentData;
    }

    public function getDepartmentDetails($currentSchool, $department_id)
    {
        $findDeparment = Department::where("school_branch_id", $currentSchool->id)->find($department_id);
        if (!$findDeparment) {
            return ApiResponseService::error("Department not found", null, 404);
        }
        return $findDeparment;
    }
}
