<?php

namespace App\Constant\Authorization\PermissionDefinations\Analytics;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnalyticsPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Analytics\AcademicAnalyticsPermissions;

class AcademicAnalyticsPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnalyticsPermissionCategories::ACADEMIC_ANALYTICS_MANAGER,
                Guards::SCHOOL_ADMIN,
                AcademicAnalyticsPermissions::VIEW_SCHOOL,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AnalyticsPermissionCategories::ACADEMIC_ANALYTICS_MANAGER,
                Guards::SCHOOL_ADMIN,
                AcademicAnalyticsPermissions::VIEW_STUDENT,
                "View",
                ""
            ),
        ];
    }
}
