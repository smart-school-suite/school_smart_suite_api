<?php

namespace App\Constant\Authorization\Permissions\GradeScale;

class ResitExamGradeScalePermissions
{
    public const ADD = "resit_exam_grade_scale.add";
    public const REMOVE = "resit_exam_grade_scale.remove";
    public const VIEW = "resit_exam_grade_scale.view";
    public const UPDATE = "resit_exam_grade_scale.update";
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
