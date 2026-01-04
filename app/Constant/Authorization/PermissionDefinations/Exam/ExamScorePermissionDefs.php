<?php

namespace App\Constant\Authorization\PermissionDefinations\Exam;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Exam\ExamScorePermissions;

class ExamScorePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_SCORE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamScorePermissions::CA_VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_SCORE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamScorePermissions::EXAM_VIEW,
                "View",
                ""
            ),
        ];
    }
}
