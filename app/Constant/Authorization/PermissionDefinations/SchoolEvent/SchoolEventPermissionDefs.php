<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolEvent;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolEventPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolEvent\SchoolEventPermissions;

class SchoolEventPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::STUDENT,
                SchoolEventPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::TEACHER,
                SchoolEventPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventPermissions::UPDATE,
                "Update",
                ""
            ),

        ];
    }
}
