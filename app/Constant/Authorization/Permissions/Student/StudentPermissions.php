<?php

namespace App\Constant\Authorization\Permissions\Student;

class StudentPermissions
{
    public const CREATE = "student.create";
    public const VIEW = "student.view";
    public const UPDATE =  "student.update";
    public const DELETE = "student.delete";
    public const DEACTIVATE = "student.deactivate";
    public const ACTIVATE = "student.activate";
    public const PROFILE_UPDATE = "student.profile_update";
    public const AVATAR_DELETE = "student.avatar_delete";
    public const AVATAR_UPLOAD = "student.avatar_upload";
    public const CHANGE_PASSWORD = "student.change_password";
    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::ACTIVATE,
            self::DEACTIVATE,
            self::PROFILE_UPDATE,
            self::AVATAR_UPLOAD,
            self::AVATAR_DELETE,
            self::CHANGE_PASSWORD
        ];
    }
}
