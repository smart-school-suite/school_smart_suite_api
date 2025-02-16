<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use App\Models\Schooladmin;

class RoleService
{
    // Implement your logic here
    public function createRole(array $data)
    {
        $role = new Role();
        $role->name = $data["name"];
        $role->guard_name = $data["guard_name"];
        $role->save();
        return $role;
    }

    public function deleteRole(string $roleId)
    {
        $roleExists = Role::find($roleId);
        if (!$roleExists) {
            return ApiResponseService::error("Role Not Found", null, 404);
        }
        $roleExists->delete();
        return $roleExists;
    }

    public function updateRole(array $data, string $roleId)
    {
        $roleExists = Role::find($roleId);
        if (!$roleExists) {
            return ApiResponseService::error("Role Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $roleExists->update($filteredData);
        return $roleExists;
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function assignRolesSchoolAdmin(array $roles, string $userId, $currentSchool)
    {
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if (!$schoolAdminExist) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $assignRoles = $schoolAdminExist->assignRole($roles);
        return $assignRoles;
    }

    public function removeRole(string $role, string $userId, $currentSchool)
    {
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if (!$schoolAdminExist) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $removeRole = $schoolAdminExist->removeRole($role);
        return $removeRole;
    }
}
