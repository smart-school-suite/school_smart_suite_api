<?php

namespace App\Services;

use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;

class SchoolAdminService
{
    // Implement your logic here

    public function updateSchoolAdmin(array $data, $schoolAdminId, $currentSchool)
    {
        $SchoolAdminExists = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if ($SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $SchoolAdminExists->update($filterData);
        return $SchoolAdminExists;
    }

    public function deleteSchoolAdmin($schoolAdminId, $currentSchool)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if ($SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $SchoolAdminExists->delete();
    }

    public function getSchoolAdmins($currentSchool)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)->get();
        return $SchoolAdminExists;
    }

    public function getSchoolAdminDetails($currentSchool, $schoolAdminId)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if ($SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        return $SchoolAdminExists;
    }

    public function createSchoolAdmin(array $data)
    {
        $new_school_admin_instance = new Schooladmin();
        $new_school_admin_instance->name = $data["name"];
        $new_school_admin_instance->email = $data["email"];
        $new_school_admin_instance->password = Hash::make($data["password"]);
        $new_school_admin_instance->role = $data["role"];
        $new_school_admin_instance->employment_status = $data["employment_status"];
        $new_school_admin_instance->hire_date = $data["hire_date"];
        $new_school_admin_instance->work_location = $data["work_location"];
        $new_school_admin_instance->position = $data["position"];
        $new_school_admin_instance->salary = $data["salary"];
        $new_school_admin_instance->school_branch_id = $data["school_branch_id"];
        $new_school_admin_instance->save();
        return $new_school_admin_instance;
    }
}
