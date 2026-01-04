<?php

namespace App\Constant\Authorization\PermissionDefinations\Department;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Department\DepartmentPermissions;

class DepartmentPermisionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::DEPARTMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                DepartmentPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
