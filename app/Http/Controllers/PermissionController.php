<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HttP\Requests\PermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Requests\AssignPermissionRequest;
use App\Http\Requests\RevokePermissionRequest;
use App\Services\ApiResponseService;
use App\Services\PermissionService;

class PermissionController extends Controller
{
    //
    protected PermissionService $permissionService;
    public function __construct(PermissionService $permissionService){
        $this->permissionService = $permissionService;
    }

    public function createPermission(PermissionRequest $request){
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

    public function givePermissionToSchoolAdmin(AssignPermissionRequest $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get('currentSchool');
        $grantSchoolAdminPermissions = $this->permissionService->givePermissionToAdmin($request->permissions, $schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Permission Granted Successfully", $grantSchoolAdminPermissions, null, 200);
    }

    public function revokePermission(RevokePermissionRequest $request, string $schoolAdminId){
        $currentSchool = $request->attributes->get('currentSchool');
        $revokePermission = $this->permissionService->revokePermission($request->permissions, $schoolAdminId, $currentSchool);
        return ApiResponseService::success("School Admin Permission Revoked Succesfully", $revokePermission, null, 200);
    }

}
