<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionCandidatePermissions;

class ElectionCandidatePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionCandidatePermissions::DIQUALIFY,
                "Disqualify",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_CANDIDATE_MANAGER,
                Guards::STUDENT,
                ElectionCandidatePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_CANDIDATE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionCandidatePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
