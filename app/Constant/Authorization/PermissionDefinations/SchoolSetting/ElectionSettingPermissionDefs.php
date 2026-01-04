<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSetting;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSetting\ElectionSettingPermissions;

class ElectionSettingPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_ELECTION_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionSettingPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_ELECTION_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionSettingPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
