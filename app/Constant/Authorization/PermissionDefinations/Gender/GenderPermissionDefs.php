<?php

namespace App\Constant\Authorization\PermissionDefinations\Gender;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Gender\GenderPermissions;

class GenderPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                Guards::APP_ADMIN,
                GenderPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                Guards::APP_ADMIN,
                GenderPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                Guards::APP_ADMIN,
                GenderPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                Guards::APP_ADMIN,
                GenderPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::GENDER_MANAGER,
                Guards::SCHOOL_ADMIN,
                GenderPermissions::DELETE,
                "View",
                ""
            ),
        ];
    }
}
