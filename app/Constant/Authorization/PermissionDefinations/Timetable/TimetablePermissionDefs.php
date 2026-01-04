<?php

namespace App\Constant\Authorization\PermissionDefinations\Timetable;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\AcademicPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Timetable\TimetablePermissions;

class TimetablePermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TimetablePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TimetablePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TimetablePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::TEACHER,
                TimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                AcademicPermissionCategories::SEMESTER_TIMETABLE_MANAGER,
                Guards::STUDENT,
                TimetablePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
