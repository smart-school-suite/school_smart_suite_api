<?php

namespace App\Constant\Authorization\PermissionDefinations\Notification;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Notification\SchoolAdminNotificationPermissions;

class SchoolAdminNotificationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_NOTIFICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminNotificationPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_NOTIFICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminNotificationPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_NOTIFICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminNotificationPermissions::READ,
                "Read",
                ""
            ),
        ];
    }
}
