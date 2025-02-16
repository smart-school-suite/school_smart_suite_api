<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\RemoveRoleRequest;
use App\Services\ApiResponseService;

class RoleController extends Controller
{
    //
    protected RoleService $roleService;
    public function __construct(RoleService $roleService){
        $this->roleService = $roleService;
    }

    public function updateRole(UpdateRoleRequest $request, string $roleId){
        $updateRole = $this->roleService->updateRole($request->validated(), $roleId);
        return ApiResponseService::success("Role Updated Sucessfully", $updateRole, null, 200);
    }

    public function getRoles(){
        $getRoles = $this->roleService->getRoles();
        return ApiResponseService::success("Roles Fetched Sucessfully", $getRoles, null, 200);
    }

    public function createRole(RoleRequest $request){
        $createRole = $this->roleService->createRole($request->validated());
        return ApiResponseService::success("Role Created Sucessfully", $createRole, null, 201);
    }

    public function deleteRole(string $roleId){
        $deleteRole = $this->roleService->deleteRole($roleId);
        return ApiResponseService::success("Role Deleted Succesfully", $deleteRole, null, 200);
    }

    public function assignRoleSchoolAdmin(AssignRoleRequest $request, string $schoolAdminId){
       $currentSchool = $request->attributes->get('currentSchool');
       $assignRole = $this->roleService->assignRolesSchoolAdmin($request->roles, $schoolAdminId, $currentSchool);
       return ApiResponseService::success("Role Assigned To Admin Successfully", $assignRole, null, 200);
    }

    public function removeRoleSchoolAdmin(RemoveRoleRequest $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get("currentSchool");
        $removeRole = $this->roleService->removeRole($request->role, $schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Role Removed Succesfully", $removeRole, null, 200);
    }


}
