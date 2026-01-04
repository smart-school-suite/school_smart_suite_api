<?php

namespace App\Constant\Authorization\PermissionDefinations\Exam;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Exam\ExamPermissions;

class ExamPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::TEACHER,
                ExamPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_MANAGER,
                Guards::STUDENT,
                ExamPermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
