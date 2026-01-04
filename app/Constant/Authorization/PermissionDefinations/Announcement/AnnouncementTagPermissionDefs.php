<?php

namespace App\Constant\Authorization\PermissionDefinations\Announcement;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnnouncementPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Announcement\AnnouncementTagPermissions;

class AnnouncementTagPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementTagPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementTagPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementTagPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementTagPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_TAG_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementTagPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
