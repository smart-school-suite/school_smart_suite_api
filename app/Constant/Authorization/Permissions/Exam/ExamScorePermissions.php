<?php

namespace App\Constant\Authorization\Permissions\Exam;

class ExamScorePermissions
{
    public const CA_VIEW = "ca_score.view";
    public const EXAM_VIEW = "exam_score.view";
    public static function all(): array
    {
        return [
            self::CA_VIEW,
            self::EXAM_VIEW
        ];
    }
}
