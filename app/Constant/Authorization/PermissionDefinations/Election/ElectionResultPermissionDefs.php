<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionResultPermissions;

class ElectionResultPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_RESULT_MANAGER,
                Guards::STUDENT,
                ElectionResultPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_RESULT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionResultPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_RESULT_MANAGER,
                Guards::TEACHER,
                ElectionResultPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
