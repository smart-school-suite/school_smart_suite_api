<?php

namespace App\Constant\Authorization\PermissionDefinations\GradeScale;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\GradeScale\ExamGradeScalePermissions;

class ExamGradeScalePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamGradeScalePermissions::ADD,
                "Add",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamGradeScalePermissions::REMOVE,
                "Remove",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamGradeScalePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                ExamGradeScalePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::STUDENT,
                ExamGradeScalePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::EXAM_GRADE_SCALE_MANAGER,
                Guards::TEACHER,
                ExamGradeScalePermissions::VIEW,
                "View",
                ""
            ),

        ];
    }
}
