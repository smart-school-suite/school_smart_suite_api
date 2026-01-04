<?php

namespace App\Constant\Authorization\PermissionDefinations\ActivationCode;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ActivationCode\ActivationCodePermissions;

class ActivationCodePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolPermissionCategories::ACTIVATION_CODE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ActivationCodePermissions::PURCHASE,
                "Purchase",
                ""
            ),
            PermissionBuilder::make(
                 SchoolPermissionCategories::ACTIVATION_CODE_MANAGER,
                 Guards::SCHOOL_ADMIN,
                 ActivationCodePermissions::ACTIVATE_STUDENT,
                 "Activate Student",
                 ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::ACTIVATION_CODE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ActivationCodePermissions::ACTIVATE_TEACHER,
                "Activate Teacher",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::ACTIVATION_CODE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ActivationCodePermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
