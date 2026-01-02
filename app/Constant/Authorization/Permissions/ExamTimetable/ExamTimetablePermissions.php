<?php

namespace App\Constant\Authorization\Permissions\ExamTimetable;

class ExamTimetablePermissions
{
    public const CREATE = "exam_timetable.create";
    public const UPDATE = "exam_timetable.update";
    public const DELETE = "exam_timetable.delete";
    public const VIEW = "exam_timetable.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW
        ];
    }
}
