<?php

namespace App\Constant\Authorization\PermissionDefinations\Semester;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Semester\SchoolSemesterPermissions;

class SchoolSemesterPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSemesterPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSemesterPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSemesterPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolSemesterPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::STUDENT,
                SchoolSemesterPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SCHOOL_SEMESTER_MANAGER,
                Guards::TEACHER,
                SchoolSemesterPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
