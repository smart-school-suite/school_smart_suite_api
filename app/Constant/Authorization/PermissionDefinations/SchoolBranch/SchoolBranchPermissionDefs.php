<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolBranch;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolBranch\SchoolBranchPermissions;

class SchoolBranchPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_BRANCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolBranchPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_BRANCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolBranchPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_BRANCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolBranchPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
