<?php

namespace App\Constant\Authorization\PermissionDefinations\Analytics;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnalyticsPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Analytics\OperationalAnalyticsPermissions;

class OperationalAnalyticsPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnalyticsPermissionCategories::OPERATIONAL_ANALYTICS_MANAGER,
                Guards::SCHOOL_ADMIN,
                OperationalAnalyticsPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
