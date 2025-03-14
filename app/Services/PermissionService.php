<?php

namespace App\Services;

use App\Models\Schooladmin;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    // Implement your logic here
    public function createPermission(array $data){
        $permission = new Permission();
        $permission->name = $data['name'];
        $permission->guard_name = $data['guard_name'];
        $permission->save();
        return $permission;
    }

    public function deletePermission(string $permissionId){
         $permissionExists = Permission::find($permissionId);
         if(!$permissionExists){
            return ApiResponseService::error("Permission Not Found", null, 404);
         }
         $permissionExists->delete();
         return $permissionExists;
    }

    public function updatePermission(array $data, string $permissionId){
        $permissionExists = Permission::find($permissionId);
         if(!$permissionExists){
            return ApiResponseService::error("Permission Not Found", null, 404);
         }
         $filteredData = array_filter($data);
         $permissionExists->update($filteredData);
         return $permissionExists;
    }

    public function getPermissions(){
        return Permission::all();
    }

    public function getSchoolAdminPermissions(string $userId,  $currentSchool){
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if(!$schoolAdminExist){
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $userPermissions = $schoolAdminExist->getPermissionNames();
        return   $userPermissions;
    }

    public function givePermissionToAdmin(array $permissions,  string $userId, $currentSchool){
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if(!$schoolAdminExist){
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $givePermission = $schoolAdminExist->givePermissionTo($permissions);
        return $givePermission;
    }

    public function revokePermission(array $permissions,  string $userId, $currentSchool){
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if(!$schoolAdminExist){
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
       $revokePermission = $schoolAdminExist->revokePermissionTo($permissions);
        return $revokePermission;
    }

}
