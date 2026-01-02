<?php

namespace App\Constant\Authorization\Permissions\ExamType;

class ExamTypePermissions
{
    public const CREATE = "exam_type.create";
    public const DELETE = "exam_type.delete";
    public const UPDATE = "exam_type.update";
    public const VIEW = "exam_type.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
