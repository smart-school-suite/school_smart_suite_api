<?php

namespace App\Constant\Authorization\Permissions\GradeScale;

class SchoolGradeScalePermissions
{
    public const CREATE = "school_grade_scale.create";
    public const VIEW = "school_grade_scale.view";
    public const DELETE = "school_grade_scale.delete";
    public const AUTO_GEN = "school_grade_scale.auto_generate";
    public static function all(): array
    {
        return [
            self::CREATE,
            self::VIEW,
            self::DELETE
        ];
    }
}
