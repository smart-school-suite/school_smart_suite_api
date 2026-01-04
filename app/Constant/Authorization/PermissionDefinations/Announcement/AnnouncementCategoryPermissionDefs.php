<?php

namespace App\Constant\Authorization\PermissionDefinations\Announcement;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnnouncementPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Announcement\AnnouncementCategoryPermissions;

class AnnouncementCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementCategoryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),

        ];
    }
}
