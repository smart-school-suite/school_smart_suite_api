<?php

namespace App\Constant\Authorization\PermissionDefinations\SubscriptionPlan;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SubscriptionPlan\PlanFeaturePermissions;

class PlanFeaturePermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_FEATURE_MANAGER,
                Guards::APP_ADMIN,
                PlanFeaturePermissions::ASSIGN,
                "Assign",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_FEATURE_MANAGER,
                Guards::APP_ADMIN,
                PlanFeaturePermissions::REMOVE,
                "Remove",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_FEATURE_MANAGER,
                Guards::APP_ADMIN,
                PlanFeaturePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
