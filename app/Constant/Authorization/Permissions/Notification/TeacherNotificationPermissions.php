<?php

namespace App\Constant\Authorization\Permissions\Notification;

class TeacherNotificationPermissions
{
    public const VIEW = "teacher_notification.view";
    public const DELETE = "teacher_notification.delete";
    public const READ = "teacher_notification.read";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE,
            self::READ
        ];
    }
}
