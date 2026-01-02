<?php

namespace App\Constant\Authorization\Permissions\Exam;

class ExamPermissions
{
    public const CREATE = "exam.create";
    public const UPDATE = "exam.update";
    public const DELETE = "exam.delete";
    public const VIEW = "exam.view";
    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
        ];
    }
}
