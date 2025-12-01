<?php

namespace App\Services\SchoolAdmin;

use App\Models\Schooladmin;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class SchoolAdminService
{
    public function updateSchoolAdmin(array $data, $schoolAdminId, $currentSchool, $authAdmin)
    {
        $SchoolAdminExists = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $SchoolAdminExists->update($filterData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolAdmin.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolAdminManagement",
                "authAdmin" => $authAdmin,
                "data" => $SchoolAdminExists,
                "message" => "School Admin Updated",
            ]
        );
        return $SchoolAdminExists;
    }

    public function deleteSchoolAdmin($schoolAdminId, $currentSchool, $authAdmin)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $SchoolAdminExists->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolAdmin.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolAdminManagement",
                "authAdmin" => $authAdmin,
                "data" => $SchoolAdminExists,
                "message" => "School Admin Deleted",
            ]
        );
        return $SchoolAdminExists;
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

    public function createSchoolAdmin(array $data, string $schoolBranchId): SchoolAdmin
    {
        try {

            DB::beginTransaction();

            $schoolAdmin = new SchoolAdmin();
            $schoolAdminId = Str::uuid();
            $schoolAdmin->id = $schoolAdminId;
            $schoolAdmin->name = $data["name"];
            $schoolAdmin->email = $data["email"];

            $schoolAdmin->password = Hash::make($data["password"]);

            $schoolAdmin->first_name = $data["first_name"];
            $schoolAdmin->last_name = $data["last_name"];
            $schoolAdmin->school_branch_id = $schoolBranchId;
            $schoolAdmin->save();
            DB::commit();
            $user = Schooladmin::where("school_branch_id", $schoolBranchId)
                ->find($schoolAdminId);
            $user->assignRole("schoolSuperAdmin");

            return $schoolAdmin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function uploadProfilePicture($request, $authSchoolAdmin)
    {
        $schoolAdminExists = Schooladmin::find($authSchoolAdmin->id);
        if (!$schoolAdminExists) {
            return ApiResponseService::error("School Admin Not found", null, 400);
        }
        try {
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteProfilePicture($authSchoolAdmin)
    {
        $schoolAdminExists = Schooladmin::find($authSchoolAdmin->id);
        if (!$schoolAdminExists) {
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

    public function deactivateAccount(string $schoolAdminId, $currentSchool, $authAdmin)
    {
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolAdminId);
        $schoolAdmin->status = 'inactive';
        $schoolAdmin->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolAdmin.deactivate"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolAdminManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolAdmin,
                "message" => "School Admin Account Deactivated",
            ]
        );
        return $schoolAdmin;
    }

    public function activateAccount(string $schoolAdminId, $currentSchool, $authAdmin)
    {
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolAdminId);
        $schoolAdmin->status = "active";
        $schoolAdmin->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolAdmin.activate"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolAdminManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolAdmin,
                "message" => "School Admin Account Activated",
            ]
        );
        return $schoolAdmin;
    }

    public function bulkUpdateSchoolAdmin(array $schoolAdminList, $currentSchool, $authAdmin)
    {
        try {
            DB::beginTransaction();
            $updateSchoolAdmin = [];
            foreach ($schoolAdminList as $schoolAdmin) {
                $admin = SchoolAdmin::where("school_branch_id", $currentSchool->id)
                    ->find($schoolAdmin['id']);
                if ($admin) {
                    $updateData = array_filter($schoolAdmin, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($updateData)) {
                        $admin->update($updateData);
                    }
                }
                $updateSchoolAdmin[] = [
                    "admin_name" => $admin->name,
                    "admin_email" => $admin->email,
                ];
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolAdmin.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolAdminManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $updateSchoolAdmin,
                    "message" => "School Admin Updated",
                ]
            );
            return $updateSchoolAdmin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeleteSchoolAdmin(array $deleteAdminList, $currentSchool, $authAdmin)
    {
        $deletedSchoolAdmin = [];

        try {
            DB::beginTransaction();
            foreach ($deleteAdminList as $adminData) {
                $schoolAdmin = SchoolAdmin::where("school_branch_id", $currentSchool->id)
                    ->find($adminData['school_admin_id']);
                $schoolAdmin->delete();
                $deletedSchoolAdmin[] = [
                    "school_admin_name" => $schoolAdmin->name,
                    'school_amdin_email' => $schoolAdmin->email
                ];
            }

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolAdmin.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolAdminManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $deletedSchoolAdmin,
                    "message" => "School Admin Deleted",
                ]
            );
            return $deletedSchoolAdmin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeactivateSchoolAdmin(array $schoolAdminList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolAdminList as $schoolAdminData) {
                $schoolAdmin = SchoolAdmin::where("school_branch_id", $currentSchool->id)
                    ->find($schoolAdminData['school_admin_id']);
                $schoolAdmin->status = "inactive";
                $schoolAdmin->save();
                $result[] = [
                    "school_admin_name" => $schoolAdmin->name,
                    "school_admin_email" => $schoolAdmin->email,
                    "account_status" => $schoolAdmin->status
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolAdmin.deactivate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolAdminManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $schoolAdmin,
                    "message" => "School Admin Account Deactivated",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkActivateSchoolAdmin(array $schoolAdminList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolAdminList as $schoolAdminData) {
                $schoolAdmin = SchoolAdmin::where("school_branch_id", $currentSchool->id)
                    ->find($schoolAdminData['school_admin_id']);
                $schoolAdmin->status = "active";
                $schoolAdmin->save();
                $result[] = [
                    "school_admin_name" => $schoolAdmin->name,
                    "school_admin_email" => $schoolAdmin->email,
                    "account_status" => $schoolAdmin->status
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolAdmin.activate"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolAdminManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $schoolAdmin,
                    "message" => "School Admin Account Activated",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
