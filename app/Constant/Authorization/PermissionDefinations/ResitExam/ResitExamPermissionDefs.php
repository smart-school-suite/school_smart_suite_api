<?php

namespace App\Constant\Authorization\PermissionDefinations\ResitExam;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\ResitExam\ResitExamPermissions;

class ResitExamPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                Guards::TEACHER,
                ResitExamPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                Guards::STUDENT,
                ResitExamPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamPermissions::UPDATE,
                "Update",
                ""
            ),
        ];
    }
}
