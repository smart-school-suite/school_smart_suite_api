<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Schooladmin;
use Spatie\Permission\Models\Permission;

class roleManagementController extends Controller
{
    //
    public function fetch_permissions(Request $request)
    {
        $permissions = Permission::all();
        if ($permissions->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "No permissions found"
            ], 400);
        }
        return response()->json([
            "status" => "success",
            "message" => "permissions fetched succefully",
            "permissions_data" => $permissions
        ], 200);
    }

    public function fetch_roles(Request $request)
    {
        $roles = Role::all();
        if ($roles->isEmpty()) {
            return response()->json([
                "status" => "error",
                "message" => "No roles found"
            ], 400);
        }
        return response()->json([
            "status" => "success",
            "message" => "roles fetched sucessfully",
            "role_data" => $roles
        ]);
    }

    /**
     * Assign permissions to a specific school admin (user).
     *
     * @param  Request  $request
     * @param  string  $schoolAdminId
     * @return \Illuminate\Http\JsonResponse
     */

    public function assignPermissionsToSchoolAdmin(Request $request, $schoolAdminId)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);



        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);

        if (!$schoolAdmin || !$schoolAdmin->hasRole('schoolAdmin')) {
            return response()->json([
                'status' => "error",
                'message' => 'School admin not found or does not have the correct role.'
            ], 404);
        }


        $schoolAdmin->revokePermissionTo($schoolAdmin->getAllPermissions());


        foreach ($request->input('permissions') as $permission) {
            $schoolAdmin->givePermissionTo($permission);
        }

        return response()->json([
            'message' => 'Permissions assigned to school admin successfully.'
        ]);
    }

        /**
     * Get all permissions of a specific school admin (user).
     *
     * @param  string  $schoolAdminId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionsOfSchoolAdmin(Request $request, $schoolAdminId)
    {

        $currentSchool = $request->attributes->get('currentSchool');

        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);

        if (!$schoolAdmin || !$schoolAdmin->hasRole('schoolAdmin')) {
            return response()->json([
                "status" => "ok",
                'message' => 'School admin not found or does not have the correct role.'
            ], 404);
        }


        $permissions = $schoolAdmin->getAllPermissions()->pluck('name');

        return response()->json([
             "status" => "ok",
             "message" => "Permission fetched successfully",
             'schoolAdminId' => $schoolAdminId,
            'permissions' => $permissions,
        ]);
    }

        /**
     * Revoke all permissions of a specific school admin (user).
     *
     * @param  int  $schoolAdminId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAllPermissionsOfSchoolAdmin(Request $request, $schoolAdminId)
    {

        $currentSchool = $request->attributes->get('currentSchool');

        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);

        if (!$schoolAdmin || !$schoolAdmin->hasRole('schoolAdmin')) {
            return response()->json([
                 "status" => "error",
                 'message' => 'School admin not found or does not have the correct role.'
            ], 404);
        }


        $schoolAdmin->syncPermissions([]);

        return response()->json([
            'schoolAdminId' => $schoolAdminId,
            'message' => 'All permissions revoked successfully.',
        ]);
    }

        /**
     * Revoke specific permissions of a school admin (user).
     *
     * @param  string  $schoolAdminId
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeSpecificPermissionsOfSchoolAdmin($schoolAdminId, Request $request)
    {

        $currentSchool = $request->attributes->get('currentSchool');

        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($schoolAdminId);

        if (!$schoolAdmin || !$schoolAdmin->hasRole('schoolAdmin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'School admin not found or does not have the correct role.'
            ], 404);
        }


        $permissionsToRevoke = $request->input('permissions', []);


        foreach ($permissionsToRevoke as $permissionName) {
            $permission = Permission::findByName($permissionName);
            if (!$permission) {
                return response()->json(['message' => "Permission '$permissionName' does not exist."], 400);
            }
        }

        $schoolAdmin->revokePermissionTo($permissionsToRevoke);

        return response()->json([
            'schoolAdminId' => $schoolAdminId,
            'permissionsRevoked' => $permissionsToRevoke,
            'message' => 'Permissions revoked successfully.',
        ]);
    }

    public function assignSuperAdminRole($schoolAdminId)
    {

        $schoolAdmin = Schooladmin::find($schoolAdminId);

        if (!$schoolAdmin) {
            return response()->json(['message' => 'School admin not found.'], 404);
        }

        if ($schoolAdmin->hasRole('schoolSuperAdmin')) {
            return response()->json(['message' => 'User is already a superAdmin.'], 400);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'superAdmin']);
        $schoolAdmin->assignRole($superAdminRole);

        return response()->json([
            'schoolAdminId' => $schoolAdminId,
            'message' => 'Role of superAdmin assigned successfully.',
        ]);
    }
}
