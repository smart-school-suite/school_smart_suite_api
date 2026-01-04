<?php

namespace App\Constant\Authorization\PermissionDefinations\Level;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Level\LevelPermissions;

class LevelPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::LEVEL_MANAGER,
                Guards::APP_ADMIN,
                LevelPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LEVEL_MANAGER,
                Guards::APP_ADMIN,
                LevelPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LEVEL_MANAGER,
                Guards::APP_ADMIN,
                LevelPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LEVEL_MANAGER,
                Guards::APP_ADMIN,
                LevelPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LEVEL_MANAGER,
                Guards::SCHOOL_ADMIN,
                LevelPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
