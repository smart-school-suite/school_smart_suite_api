<?php

namespace App\Constant\Authorization\PermissionCategory\PermissionCategoryDefination;

use App\Constant\Authorization\Builder\PermissionCategoryBuilder;
use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnnouncementPermissionCategories;

class AnnouncementPermissionCategoryDefinations
{
    public static function all(): array
    {
        return [
            PermissionCategoryBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                "Announcement Category Manager",
                "Allows creation and organization of primary news channels, such as 'General News', 'Emergency Alerts', or 'Event Notices'."
            ),
            PermissionCategoryBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                "Announcement Label Manager",
                "Grants control over visual status indicators like 'Urgent', 'New', or 'Expired' to highlight specific messages."
            ),
            PermissionCategoryBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_MANAGER,
                "Announcement Manager",
                "Provides full authority to draft, schedule, publish, and delete the actual content of school-wide announcements."
            ),
            PermissionCategoryBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                "Announcement Tag Manager",
                "Enables the management of keywords and hashtags used to make announcements searchable and filterable by users."
            )
        ];
    }
}
