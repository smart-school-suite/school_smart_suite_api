<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSetting;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSetting\ExamSettingPermissions;

class ExamSettingPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_EXAM_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamSettingPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_EXAM_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamSettingPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
