<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolEvent;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolEventPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolEvent\SchoolEventCategoryPermissions;

class SchoolEventCategoryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventCategoryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventCategoryPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventCategoryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventCategoryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_CATEGORY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventCategoryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
        ];
    }
}
