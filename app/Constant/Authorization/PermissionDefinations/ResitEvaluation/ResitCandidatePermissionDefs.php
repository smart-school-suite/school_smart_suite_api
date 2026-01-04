<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitEvaluation;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitEvaluation\ResitCandidatePermissions;

class ResitCandidatePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitCandidatePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitCandidatePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitCandidatePermissions::EXEMTED,
                "Exemted",
                ""
            ),
        ];
    }
}
