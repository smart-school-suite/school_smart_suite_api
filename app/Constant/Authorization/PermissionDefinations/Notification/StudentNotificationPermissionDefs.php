<?php

namespace App\Constant\Authorization\PermissionDefinations\Notification;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\StudentPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Notification\StudentNotificationPermissions;

class StudentNotificationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_NOTIFICATION_MANAGER,
                Guards::STUDENT,
                StudentNotificationPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_NOTIFICATION_MANAGER,
                Guards::STUDENT,
                StudentNotificationPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_NOTIFICATION_MANAGER,
                Guards::STUDENT,
                StudentNotificationPermissions::READ,
                "Read",
                ""
            ),

        ];
    }
}
