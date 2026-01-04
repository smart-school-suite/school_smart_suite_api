<?php

namespace App\Constant\Authorization\PermissionDefinations\ExamTimetable;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ExamTimetable\ExamTimetablePermissions;

class ExamTimetablePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::TEACHER,
                ExamTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_TIMETABLE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamTimetablePermissions::CREATE,
                "Create",
                ""
            ),
        ];
    }
}
