<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolEvent;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolEventPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolEvent\SchoolEventTagPermissions;

class SchoolEventTagPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolEventTagPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                SchoolEventPermissionCategories::SCHOOL_EVENT_TAG_MANAGER,
                Guards::APP_ADMIN,
                SchoolEventTagPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
        ];
    }
}
