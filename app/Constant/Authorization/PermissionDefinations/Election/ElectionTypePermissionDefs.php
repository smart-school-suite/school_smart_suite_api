<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionTypePermissions;

class ElectionTypePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::DELETE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_TYPE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionTypePermissions::DEACTIVATE,
                "Create",
                ""
            ),
        ];
    }
}
