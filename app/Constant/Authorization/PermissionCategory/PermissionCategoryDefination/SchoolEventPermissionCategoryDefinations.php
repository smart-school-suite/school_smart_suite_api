<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolEventPermissionCategories;

class SchoolEventPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                "School Event Category Manager",
                "Allows the creation and organization of event types such as 'Sports,' 'Academic,' 'Holidays,' or 'Social' to help users filter the school calendar."
            ),
            PermissionCategoryBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                "School Event Manager",
                "Grants full authority to create, schedule, update, and cancel specific school events and activities."
            ),
            PermissionCategoryBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                "School Event Tag Manager",
                "Enables the management of searchable keywords and labels used to group events and make them easier for students and staff to find."
            )
        ];
    }
}
