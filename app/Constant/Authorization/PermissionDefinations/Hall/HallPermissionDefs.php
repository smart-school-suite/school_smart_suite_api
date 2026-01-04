<?php

namespace App\Constant\Authorization\PermissionDefinations\Hall;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Hall\HallPermissions;

class HallPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::DELETE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::DEACTIVATE_HALL,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                HallPermissions::ACTIVATE_HALL,
                "Activate",
                ""
            ),
        ];
    }
}
