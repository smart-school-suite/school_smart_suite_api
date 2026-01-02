<?php

namespace App\Constant\Authorization\Permissions\Teacher;

class TeacherSpecialtyPermissions
{
    public const ASSIGN = "teacher_specialty.assign";
    public const REMOVE = "teacher_specialty.remove";
    public const VIEW = "teacher_specialty.view";
    public const VIEW_ASSIGNED = "teacher_specialty.view_assigned";
    public const VIEW_UNASSIGNED = "teacher_specialty.view_unassigned";
    public static function all(): array
    {
        return [
            self::ASSIGN,
            self::REMOVE,
            self::VIEW,
            self::VIEW_ASSIGNED,
            self::VIEW_UNASSIGNED
        ];
    }
}
