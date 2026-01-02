<?php

namespace App\Constant\Authorization\Permissions\Course;

class TeacherCoursePermissions
{
    public const ASSIGN = "teacher_course.assign";
    public const REMOVE = "teacher_course.remove";
    public const VIEW = "teacher_course.view";
    public static function all(): array
    {
        return [
            self::ASSIGN,
            self::REMOVE,
            self::VIEW
        ];
    }
}
