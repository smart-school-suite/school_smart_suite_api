<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;

class SchoolPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                "School Admin Manager",
                "Grants authority to create and manage administrator accounts, assign staff roles, and control system access levels."
            ),
            PermissionCategoryBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_NOTIFICATION_MANAGER,
                "School Admin Notification Manager",
                "Allows configuration of system alerts, automated emails, and internal communication preferences for the administrative team."
            ),
            PermissionCategoryBuilder::make(
                SchoolPermissionCategories::SCHOOL_BRANCH_MANAGER,
                "School Branch Manager",
                "Provides control over specific campus locations, including branch-specific contact details and regional settings."
            ),
            PermissionCategoryBuilder::make(
                SchoolPermissionCategories::SCHOOL_MANAGER,
                "Institution Manager",
                "High-level permission to manage the global school profile, branding, and core institutional configurations."
            ),
            PermissionCategoryBuilder::make(
                SchoolPermissionCategories::ACTIVATION_CODE_MANAGER,
                "Activation Code Manager",
                "Enables the generation and tracking of security or license codes required for software activation and user registration."
            )
        ];
    }
}
