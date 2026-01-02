<?php

namespace App\Constant\Authorization\Permissions\ExamEvaluation;

class ExamEvaluationPermissions
{
    public const ENTER = "exam_evaluation.enter";
    public const UPDATE  = "exam_evaluation.update";

    public static function all(): array
    {
        return [
            self::ENTER,
            self::UPDATE
        ];
    }
}
