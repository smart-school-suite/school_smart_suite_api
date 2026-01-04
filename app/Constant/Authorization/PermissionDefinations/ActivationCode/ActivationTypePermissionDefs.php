<?php

namespace App\Constant\Authorization\PermissionDefinations\ActivationCode;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ActivationCode\ActivationCodeTypePermissions;

class ActivationTypePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ActivationCodeTypePermissions::VIEW,
                "View",
                ""
            ),

            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::ACTIVATION_CODE_TYPE_MANAGER,
                Guards::APP_ADMIN,
                ActivationCodeTypePermissions::CREATE,
                "Activate",
                ""
            ),

        ];
    }
}
