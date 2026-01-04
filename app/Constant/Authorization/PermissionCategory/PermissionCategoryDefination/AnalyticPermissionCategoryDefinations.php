<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnalyticsPermissionCategories;

class AnalyticPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                AnalyticsPermissionCategories::ACADEMIC_ANALYTICS_MANAGER,
                "Academic Analytics Manager",
                "Grants access to student performance trends, grading distributions, and attendance metrics."
            ),
            PermissionCategoryBuilder::make(
                AnalyticsPermissionCategories::FINANCIAL_ANALYTICS_MANAGER,
                "Financial Analytics Manager",
                "Allows viewing and reporting on revenue, fee collections, outstanding balances, and institutional expenses."
            ),
            PermissionCategoryBuilder::make(
                AnalyticsPermissionCategories::OPERATIONAL_ANALYTICS_MANAGER,
                "Operational Analytics Manager",
                "Provides insights into institutional efficiency, resource utilization, and staff/facility workload patterns."
            )
        ];
    }
}
