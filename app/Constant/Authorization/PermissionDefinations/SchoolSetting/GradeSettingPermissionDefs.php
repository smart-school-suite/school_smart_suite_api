<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSetting;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSetting\GradeSettingPermissions;

class GradeSettingPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_GRADE_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                GradeSettingPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_GRADE_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                GradeSettingPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
