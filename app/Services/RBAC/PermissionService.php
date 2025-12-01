<?php

namespace App\Services\RBAC;

use App\Models\Schooladmin;
use Spatie\Permission\Models\Permission;
use App\Models\Permission as AppPermission;
use Exception;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class PermissionService
{
    public function createPermission(array $data)
    {
        $permission = new AppPermission();
        $permission->name = $data['name'];
        $permission->permission_category_id = $data['permission_category_id'];
        $permission->guard_name = $data['guard_name'];
        $permission->save();
        return $permission;
    }

    public function deletePermission(string $permissionId)
    {
        $permissionExists = Permission::find($permissionId);
        if (!$permissionExists) {
            return ApiResponseService::error("Permission Not Found", null, 404);
        }
        $permissionExists->delete();
        return $permissionExists;
    }

    public function updatePermission(array $data, string $permissionId)
    {
        $permissionExists = Permission::find($permissionId);
        if (!$permissionExists) {
            return ApiResponseService::error("Permission Not Found", null, 404);
        }
        $filteredData = array_filter($data);
        $permissionExists->update($filteredData);
        return $permissionExists;
    }

    public function getPermissions()
    {
        return AppPermission::with('permissionCategory')->all();
    }

    public function getSchoolAdminPermissions(string $userId,  $currentSchool)
    {
        $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);

        if (!$schoolAdmin) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }

        $permissionNames = $schoolAdmin->getPermissionNames();

        $permissions = AppPermission::with('permissionCategory')
            ->whereIn('name', $permissionNames)
            ->get();

        $grouped = $permissions->groupBy(fn($perm) => optional($perm->permissionCategory)->id)
            ->map(function ($items, $categoryId) {
                $category = optional($items->first()->permissionCategory);

                return [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->title,
                    ],
                    'permissions' => $items->map(function ($perm) {
                        return [
                            'id' => $perm->uuid,
                            'name' => $perm->name,
                            'description' => $perm->description ?? 'Nice permission',
                        ];
                    })->values(),
                ];
            })->values();
        return $grouped;
    }

    public function givePermissionToAdmin(array $permissions,  string $userId, $currentSchool, $authAdmin)
    {
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if (!$schoolAdminExist) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $givePermission = $schoolAdminExist->givePermissionTo($permissions);
        AdminActionEvent::dispatch(
            [
                "permissions" => ["schoolAdmin.schoolAdmin.permission.assign"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature" => "schoolAdminRBACManagement",
                "authAdmin" => $authAdmin,
                "data" => [
                    "permissions" => $permissions,
                    "school_admin" => $schoolAdminExist
                ],
                "message" => "Permission Assigned to Admin",
            ]
        );
        return $givePermission;
    }

    public function revokePermission(array $permissions,  string $userId, $currentSchool, $authAdmin)
    {
        $schoolAdminExist = Schooladmin::where("school_branch_id", $currentSchool->id)->find($userId);
        if (!$schoolAdminExist) {
            return ApiResponseService::error("School Admin Not Found", null, 404);
        }
        $revokePermission = $schoolAdminExist->revokePermissionTo($permissions);
        AdminActionEvent::dispatch(
            [
                "permissions" => ["schoolAdmin.schoolAdmin.permission.revoke"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature" => "schoolAdminRBACManagement",
                "authAdmin" => $authAdmin,
                "data" => [
                    "permissions" => $revokePermission,
                    "school_admin" => $schoolAdminExist
                ],
                "message" => "Admin Permission Revoked",
            ]
        );
        return $revokePermission;
    }

    public function getAssignablePermission(string $schoolAdminId, $currentSchool)
    {
        try {
            $schoolAdmin = Schooladmin::where("school_branch_id", $currentSchool->id)->findOrFail($schoolAdminId);
            $allPermissions = AppPermission::with('permissionCategory')->where("guard_name", "schooladmin")->get();
            $assignedPermissions = $schoolAdmin->getAllPermissions();
            $assignedPermissionUuids = $assignedPermissions->pluck('uuid')->toArray();

            $assignablePermissions = $allPermissions->reject(function ($permission) use ($assignedPermissionUuids) {
                return in_array($permission->uuid, $assignedPermissionUuids);
            });

            $groupedPermissions = $assignablePermissions->groupBy(function ($permission) {
                return $permission->permissionCategory ? $permission->permissionCategory->id : 'no_category';
            });

            $responseCategories = [];

            foreach ($groupedPermissions as $categoryUuid => $permissionsInGroup) {
                $categoryData = null;
                $permissionsList = [];

                if ($categoryUuid !== 'no_category') {
                    $firstPermission = $permissionsInGroup->first();
                    if ($firstPermission && $firstPermission->permissionCategory) {
                        $categoryData = [
                            'id' => $firstPermission->permissionCategory->id,
                            'name' => $firstPermission->permissionCategory->title,
                        ];
                    }
                } else {
                    $categoryData = [
                        'id' => 'no_category',
                        'name' => 'No Category',
                    ];
                }

                foreach ($permissionsInGroup as $permission) {
                    $permissionsList[] = [
                        'id' => $permission->uuid,
                        'name' => $permission->name,
                        'description' => "Nice permission",
                    ];
                }

                $responseCategories[] = [
                    'category' => $categoryData,
                    'permissions' => $permissionsList,
                ];
            }

            return $responseCategories;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
