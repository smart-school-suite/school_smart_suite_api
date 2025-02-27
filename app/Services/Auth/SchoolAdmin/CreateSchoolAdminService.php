<?php

namespace App\Services\Auth\SchoolAdmin;
use App\Models\Schooladmin;
use Illuminate\Support\Facades\Hash;
class CreateSchoolAdminService
{
    // Implement your logic here
    public function createSchoolAdmin($schoolAdminData, $currentSchool){
        $new_school_admin_instance = new Schooladmin();
        $new_school_admin_instance->name = $schoolAdminData["name"];
        $new_school_admin_instance->email = $schoolAdminData["email"];
        $new_school_admin_instance->role = $schoolAdminData["role"];
        $new_school_admin_instance->password = Hash::make($schoolAdminData["password"]);
        $new_school_admin_instance->employment_status = $schoolAdminData["employment_status"];
        $new_school_admin_instance->work_location = $schoolAdminData["work_location"];
        $new_school_admin_instance->position = $schoolAdminData["position"];
        $new_school_admin_instance->hire_date = $schoolAdminData["hire_date"];
        $new_school_admin_instance->salary = $schoolAdminData["salary"];
        $new_school_admin_instance->school_branch_id = $currentSchool->id;
        $new_school_admin_instance->save();
        $new_school_admin_instance->assignRole('schoolAdmin');
        return $new_school_admin_instance;
    }
}
