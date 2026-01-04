<?php

namespace App\Constant\Authorization\PermissionDefinations\Announcement;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnnouncementPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Announcement\AnnouncementPermissions;

class AnnouncementPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
