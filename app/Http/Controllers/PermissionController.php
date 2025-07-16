<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use APP\Http\Requests\Permission\CreatePermissionRequest;
use APP\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Requests\Permission\AddUserPermissionRequest;
use App\Http\Requests\Permission\RevokePermissionRequest;
use App\Services\ApiResponseService;
use App\Services\PermissionService;
use Exception;

class PermissionController extends Controller
{
    //
    protected PermissionService $permissionService;
    public function __construct(PermissionService $permissionService){
        $this->permissionService = $permissionService;
    }

    public function createPermission(CreatePermissionRequest $request){
        $createPermission = $this->permissionService->createPermission($request->validated());
        return ApiResponseService::success("Permission Created Successfully", $createPermission, null, 201);
    }

    public function deletePermission(string $permissionId){
        $deletePermission = $this->permissionService->deletePermission($permissionId);
        return ApiResponseService::success("Permission Deleted Sucessfully", $deletePermission, null, 200);
    }

    public function updatePermission(UpdatePermissionRequest $request, string $permissionId){
        $updatePermission = $this->permissionService->updatePermission($request->validated(), $permissionId);
        return ApiResponseService::success("Permission Updated Sucessfully", $updatePermission, null, 200);
    }

    public function getPermission(){
        $getPermissions = $this->permissionService->getPermissions();
        return ApiResponseService::success("Permissions Fetched Sucessfully", $getPermissions, null, 200);
    }

    public function getSchoolAdminPermissions(Request $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getSchoolAdminPermissions = $this->permissionService->getSchoolAdminPermissions($schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Permissions Fetched Sucessfully", $getSchoolAdminPermissions, null, 200);
    }

    public function givePermissionToSchoolAdmin(AddUserPermissionRequest $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get('currentSchool');
        $grantSchoolAdminPermissions = $this->permissionService->givePermissionToAdmin($request->permissions, $schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Permission Granted Successfully", $grantSchoolAdminPermissions, null, 200);
    }

    public function revokePermission(RevokePermissionRequest $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get('currentSchool');
        $revokePermission = $this->permissionService->revokePermission($request->permissions, $schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Permission Revoked Succesfully", $revokePermission, null, 200);
    }

    public function getAssignablePermissions(Request $request, $schoolAdminId){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
        $permissions = $this->permissionService->getAssignablePermission($schoolAdminId, $currentSchool);
        return ApiResponseService::success("Assignable Permissions Fetched Successfully", $permissions, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
