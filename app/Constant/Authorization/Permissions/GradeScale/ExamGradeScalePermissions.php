<?php

namespace App\Constant\Authorization\Permissions\GradeScale;

class ExamGradeScalePermissions
{
    public const ADD = "exam_grade_scale.add";
    public const REMOVE = "exam_grade_scale.remove";
    public const VIEW = "exam_grade_scale.view";
    public const UPDATE = "exam_grade_scale.update";
    public static function all(): array
    {
        return [
            self::ADD,
            self::REMOVE,
            self::VIEW,
            self::UPDATE
        ];
    }
}
