<?php

namespace App\Constant\Authorization\Permissions\ExamResult;

class ExamResultPermissions
{
    public const VIEW = "exam_results.view";
    public const DELETE = "exam_results.delete";
    public static function all(): array {
         return [
            self::VIEW,
            self::DELETE
         ];
    }
}
