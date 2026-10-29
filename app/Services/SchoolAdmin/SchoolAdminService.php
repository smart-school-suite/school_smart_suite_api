<?php

namespace App\Services\SchoolAdmin;

use App\Models\Schooladmin;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\ApiResponseService;
use App\Services\Helpers\Actor\ActorHelperService;

class SchoolAdminService
{
    public function updateSchoolAdmin(array $data, string $schoolAdminId, object $currentSchool, object $authAdmin)
    {
        $SchoolAdminExists = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $SchoolAdminExists->update($filterData);
        return $SchoolAdminExists;
    }

    public function deleteSchoolAdmin(string $schoolAdminId, object $currentSchool, object $authAdmin)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $SchoolAdminExists->delete();
        return $SchoolAdminExists;
    }

    public function getSchoolAdmins(object $currentSchool)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)
            ->with(['gender', 'roles', 'currentAvatar'])->get();
        return $SchoolAdminExists->map(fn($admin) => [
            "id" => $admin->id,
            "username" => $admin->username ?? null,
            "first_name" => $admin->first_name ?? null,
            "last_name" => $admin->last_name ?? null,
            "names" => $admin->name ?? null,
            "email" => $admin->email ?? null,
            "phone" => $admin->phone ?? null,
            "address" => $admin->address ?? null,
            "profile_picture" => $admin->currentAvatar?->object_key ?? null,
            "status" => $admin->status ?? null,
            'role' => $admin->roles->first()?->name ?? 'No Role',
            "gender" => $admin->gender->name ?? null,
            "created_at" => $admin->created_at ?? null,
            "update_at" => $admin->updated_at ?? null,
        ]);
    }

    public function getSchoolAdminDetails(object $currentSchool, string $schoolAdminId)
    {
        $SchoolAdminExists =  Schooladmin::where("school_branch_id", $currentSchool->id)
            ->with(['currentAvatar'])->find($schoolAdminId);
        if (!$SchoolAdminExists) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        return $SchoolAdminExists;
    }

    public function createSchoolAdmin(
        array $data,
        string $schoolBranchId
    ): Schooladmin {

        return DB::transaction(function () use ($data, $schoolBranchId) {
            $actorHelper = app(ActorHelperService::class);
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = $data['name'] ?? 'Admin';
            }

            $username = $actorHelper->generateUsername($fullName, Schooladmin::class);

            $schoolAdmin = Schooladmin::create([
                'id' => (string) Str::uuid(),
                'name' => $data['name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'username' => $username,
                'password' => Hash::make($data['password']),
                'school_branch_id' => $schoolBranchId,
            ]);

            $schoolAdmin->assignRole('schoolSuperAdmin');

            return $schoolAdmin;
        });
    }
    public function deactivateAccount(string $schoolAdminId, object $currentSchool, object $authAdmin)
    {
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolAdminId);
        $schoolAdmin->status = 'inactive';
        $schoolAdmin->save();
        return $schoolAdmin;
    }

    public function activateAccount(string $schoolAdminId, object $currentSchool, object $authAdmin)
    {
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->findOrFail($schoolAdminId);
        $schoolAdmin->status = "active";
        $schoolAdmin->save();
        return $schoolAdmin;
    }

    public function bulkUpdateSchoolAdmin(array $schoolAdminList, object $currentSchool, object $authAdmin)
    {
        try {
            DB::beginTransaction();
            $updateSchoolAdmin = [];
            foreach ($schoolAdminList as $schoolAdmin) {
                $admin = Schooladmin::where("school_branch_id", $currentSchool->id)
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
            return $updateSchoolAdmin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeleteSchoolAdmin(array $deleteAdminList, object $currentSchool, object $authAdmin)
    {
        $deletedSchoolAdmin = [];

        try {
            DB::beginTransaction();
            foreach ($deleteAdminList as $adminData) {
                $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
                    ->find($adminData['school_admin_id']);
                $schoolAdmin->delete();
                $deletedSchoolAdmin[] = [
                    "school_admin_name" => $schoolAdmin->name,
                    'school_amdin_email' => $schoolAdmin->email
                ];
            }

            DB::commit();
            return $deletedSchoolAdmin;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeactivateSchoolAdmin(array $schoolAdminList, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolAdminList as $schoolAdminData) {
                $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
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
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkActivateSchoolAdmin(array $schoolAdminList, object $currentSchool, object $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($schoolAdminList as $schoolAdminData) {
                $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)
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
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
