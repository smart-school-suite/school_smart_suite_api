<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionPermissions;

class ElectionPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::TEACHER,
                ElectionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::STUDENT,
                ElectionPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
