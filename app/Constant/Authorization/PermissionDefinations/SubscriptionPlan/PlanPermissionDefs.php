<?php

namespace App\Constant\Authorization\PermissionDefinations\SubscriptionPlan;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SubscriptionPlan\PlanPermissions;

class PlanPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::PLAN_MANAGER,
                Guards::APP_ADMIN,
                PlanPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
