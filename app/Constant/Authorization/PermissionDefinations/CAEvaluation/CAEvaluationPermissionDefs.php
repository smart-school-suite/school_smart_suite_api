<?php

namespace App\Constant\Authorization\PermissionDefinations\CAEvaluation;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\CAEvaluation\CAEvaluationPermissions;

class CAEvaluationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::CA_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                CAEvaluationPermissions::ENTER,
                "Enter Scores",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::CA_EVALUATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                CAEvaluationPermissions::UPDATE,
                "Update Scores",
                ""
            )
        ];
    }
}
