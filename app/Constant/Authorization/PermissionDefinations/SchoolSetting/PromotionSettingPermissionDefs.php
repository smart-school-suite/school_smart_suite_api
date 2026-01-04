<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolSetting;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolSettingPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolSetting\PromotionSettingPermissions;

class PromotionSettingPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_PROMOTION_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                PromotionSettingPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolSettingPermissionCategories::SCHOOL_PROMOTION_SETTING_MANAGER,
                Guards::SCHOOL_ADMIN,
                PromotionSettingPermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
