<?php

namespace App\Constant\Authorization\Permissions\Exam;

class ExamCandidatePermissions
{
    public const VIEW = "exam_candidate.view";
    public const DISQUALIFY = "exam_candidate.disqualify";
    public const MARK_ABSENT = "exam_candidate.mark_absent";
    public static function all(): array
    {
        return [
            self::VIEW,
            self::DISQUALIFY,
            self::MARK_ABSENT
        ];
    }
}
