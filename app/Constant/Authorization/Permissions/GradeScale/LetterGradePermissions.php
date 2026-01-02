<?php

namespace App\Constant\Authorization\Permissions\GradeScale;

class LetterGradePermissions
{
    public const CREATE = "letter_grade.create";
    public const UPDATE = "letter_grade.update";
    public const VIEW = "letter_grade.view";
    public const DELETE = "letter_grade.delete";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::VIEW,
            self::DELETE
        ];
    }
}
