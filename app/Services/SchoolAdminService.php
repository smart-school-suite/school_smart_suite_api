<?php

namespace App\Services;

use App\Models\Schooladmin;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        return $SchoolAdminExists;
    }

    public function createSchoolAdmin(array $data, $schoolBranchId)
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
        $new_school_admin_instance->school_branch_id = $schoolBranchId;
        $new_school_admin_instance->save();
        return $new_school_admin_instance;
    }

    public function uploadProfilePicture($request, $authSchoolAdmin){
        $schoolAdminExists = Schooladmin::find($authSchoolAdmin->id);
        if(!$schoolAdminExists){
            return ApiResponseService::error("School Admin Not found", null, 400);
        }
         try{
            DB::transaction(function () use ($request, $schoolAdminExists) {

                if ($schoolAdminExists->profile_picture) {
                    Storage::disk('public')->delete('SchoolAdminAvatars/' . $schoolAdminExists->profile_picture);
                }
                $profilePicture = $request->file('profile_picture');
                $fileName = time() . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->storeAs('public/SchoolAdminAvatars', $fileName);

                $schoolAdminExists->profile_picture = $fileName;
                $schoolAdminExists->save();
            });
           return true;
         }
         catch(Exception $e){
             throw $e;
         }
    }

    public function deleteProfilePicture($authSchoolAdmin) {
        $schoolAdminExists = Schooladmin::find($authSchoolAdmin->id);
        if(!$schoolAdminExists){
            return ApiResponseService::error("School Admin Not found", null, 400);
        }
        if (!$schoolAdminExists->profile_picture) {
            return ApiResponseService::error("No Profile Picture to Delete {$schoolAdminExists->name}", null, 400);
        }

        try {
            Storage::disk('public')->delete('SchoolAdminAvatars/' . $schoolAdminExists->profile_picture);

            $schoolAdminExists->profile_picture = null;
            $schoolAdminExists->save();

            return $schoolAdminExists;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
