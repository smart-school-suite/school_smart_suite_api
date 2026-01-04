<?php

namespace App\Constant\Authorization\PermissionDefinations\Exam;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Exam\ExamCandidatePermissions;

class ExamCandidatePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamCandidatePermissions::DISQUALIFY,
                "Disqualify",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamCandidatePermissions::MARK_ABSENT,
                "Mark Absent",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamCandidatePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
