<?php

namespace App\Constant\Authorization\PermissionDefinations\RBAC;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\RBAC\RBACPermissionCategory;

class RBACPermissionCategoryDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::VIEW,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PERMISSION_CATEGORY_MANAGER,
                Guards::APP_ADMIN,
                RBACPermissionCategory::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
