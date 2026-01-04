<?php

namespace App\Constant\Authorization\PermissionDefinations\RBAC;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\RBAC\RBACPermissionRoles;

class RBACRolePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionRoles::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionRoles::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionRoles::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionRoles::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                RBACPermissionRoles::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ROLE_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionRoles::CREATE,
                "Create",
                ""
            ),
        ];
    }
}
