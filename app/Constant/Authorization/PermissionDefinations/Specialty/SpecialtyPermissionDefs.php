<?php

namespace App\Constant\Authorization\PermissionDefinations\Specialty;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Specialty\SpecialtyPermissions;

class SpecialtyPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SPECIALTY_MANAGER,
                Guards::SCHOOL_ADMIN,
                SpecialtyPermissions::ACTIVATE,
                "Activate",
                ""
            )
        ];
    }
}
