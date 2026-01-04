<?php

namespace App\Constant\Authorization\PermissionDefinations\SubscriptionPlan;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SubscriptionPlan\FeaturePermissions;

class FeaturePermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::FEATURE_MANAGER,
                Guards::APP_ADMIN,
                FeaturePermissions::ACTIVATE,
                "Activate",
                ""
            )
        ];
    }
}
