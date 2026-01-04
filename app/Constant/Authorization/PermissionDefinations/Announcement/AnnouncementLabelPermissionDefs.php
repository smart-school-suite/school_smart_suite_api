<?php

namespace App\Constant\Authorization\PermissionDefinations\Announcement;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AnnouncementPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Announcement\AnnouncementLabelPermissions;

class AnnouncementLabelPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementLabelPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementLabelPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementLabelPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                Guards::APP_ADMIN,
                AnnouncementLabelPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AnnouncementPermissionCategories::ANNOUNCEMENT_LABEL_MANAGER,
                Guards::SCHOOL_ADMIN,
                AnnouncementLabelPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
