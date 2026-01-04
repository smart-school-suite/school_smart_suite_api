<?php

namespace App\Constant\Authorization\PermissionDefinations\School;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\School\SchoolPermissions;

class SchoolPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolPermissions::UPDATE,
                "Update",
                ""
            ),
        ];
    }
}
