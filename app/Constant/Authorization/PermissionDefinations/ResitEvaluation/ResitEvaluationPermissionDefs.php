<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitEvaluation;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitEvaluation\ResitEvaluationPermissions;


class ResitEvaluationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitEvaluationPermissions::ENTER,
                "Enter Score",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitEvaluationPermissions::UPDATE,
                "Enter Score",
                ""
            ),
        ];
    }
}
