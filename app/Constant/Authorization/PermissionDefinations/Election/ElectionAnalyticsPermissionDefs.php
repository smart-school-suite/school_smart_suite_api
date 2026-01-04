<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionAnalyticsPermissions;

class ElectionAnalyticsPermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_ANALYTICS_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionAnalyticsPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
