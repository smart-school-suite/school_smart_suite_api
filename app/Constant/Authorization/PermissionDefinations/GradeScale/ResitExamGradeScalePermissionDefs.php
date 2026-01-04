<?php

namespace App\Constant\Authorization\PermissionDefinations\GradeScale;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\GradeScale\ResitExamGradeScalePermissions;

class ResitExamGradeScalePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamGradeScalePermissions::REMOVE,
                "Remove",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamGradeScalePermissions::ADD,
                "Add",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamGradeScalePermissions::VIEW,
                "Add",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::TEACHER,
                ResitExamGradeScalePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::STUDENT,
                ResitExamGradeScalePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::RESIT_EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ResitExamGradeScalePermissions::UPDATE,
                "Update",
                ""
            ),
        ];
    }
}
