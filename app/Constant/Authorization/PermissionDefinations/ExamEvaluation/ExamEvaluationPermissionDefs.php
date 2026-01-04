<?php

namespace App\Constant\Authorization\PermissionDefinations\ExamEvaluation;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ExamEvaluation\ExamEvaluationPermissions;

class ExamEvaluationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamEvaluationPermissions::ENTER,
                "Enter Score",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamEvaluationPermissions::UPDATE,
                "Update Score",
                ""
            ),
        ];
    }
}
