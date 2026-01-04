<?php

namespace App\Constant\Authorization\PermissionDefinations\Hall;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Hall\SpecialtyHallPermissions;

class SpecialtyHallPermissionsDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyHallPermissions::ASSIGN,
                "Assign",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyHallPermissions::REMOVE,
                "Remove",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyHallPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyHallPermissions::VIEW_ASSIGNED,
                "View Assigned",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_HALL_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyHallPermissions::VIEW_UNASSIGNED,
                "View Unassigned",
                ""
            )
        ];
    }
}
