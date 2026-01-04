<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSetting;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSetting\ResitSettingPermissions;

class ResitSettingPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_RESIT_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitSettingPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_RESIT_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitSettingPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
