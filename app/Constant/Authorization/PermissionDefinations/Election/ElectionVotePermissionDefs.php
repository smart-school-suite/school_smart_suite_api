<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionVotePermissions;

class ElectionVotePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_VOTE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionVotePermissions::VOTE,
                "Vote",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_VOTE_MANAGER,
                Guards::STUDENT,
                ElectionVotePermissions::VOTE,
                "Vote",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_VOTE_MANAGER,
                Guards::TEACHER,
                ElectionVotePermissions::VOTE,
                "Vote",
                ""
            ),

        ];
    }
}
