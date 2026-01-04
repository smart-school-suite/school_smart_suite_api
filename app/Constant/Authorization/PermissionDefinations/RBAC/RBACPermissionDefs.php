<?php

namespace App\Constant\Authorization\PermissionDefinations\RBAC;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\RBAC\RBACPermissions;
class RBACPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::VIEW,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
