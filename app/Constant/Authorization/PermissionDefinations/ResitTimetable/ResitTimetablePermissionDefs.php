<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitTimetable;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitTimetable\ResitTimetablePermissions;

class ResitTimetablePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::STUDENT,
                ResitTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::TEACHER,
                ResitTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitTimetablePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitTimetablePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitTimetablePermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
