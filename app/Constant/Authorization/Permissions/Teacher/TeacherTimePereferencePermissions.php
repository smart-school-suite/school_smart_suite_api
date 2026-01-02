<?php

namespace App\Constant\Authorization\Permissions\Teacher;

class TeacherTimePereferencePermissions
{
    public const ADD = "teacher_time_preference.add";
    public const DELETE = "teacher_time_preference.delete";
    public const UPDATE = "teacher_time_preference.update";
    public const VIEW = "teacher_time_preference.view";

    public static function all(): array
    {
        return [
            self::ADD,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
