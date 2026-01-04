<?php

namespace App\Constant\Authorization\PermissionDefinations\Notification;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\TeacherPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Notification\TeacherNotificationPermissions;

class TeacherNotificationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_NOTIFICATION_MANAGER,
                Guards::TEACHER,
                TeacherNotificationPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_NOTIFICATION_MANAGER,
                Guards::TEACHER,
                TeacherNotificationPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_NOTIFICATION_MANAGER,
                Guards::TEACHER,
                TeacherNotificationPermissions::READ,
                "Read",
                ""
            ),
        ];
    }
}
