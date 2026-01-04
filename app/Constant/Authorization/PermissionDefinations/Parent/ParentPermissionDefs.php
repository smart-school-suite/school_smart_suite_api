<?php

namespace App\Constant\Authorization\PermissionDefinations\Parent;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\StudentPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Parent\ParentPermissions;

class ParentPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                StudentPermissionCategories::PARENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ParentPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::PARENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ParentPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::PARENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ParentPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::PARENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                ParentPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
