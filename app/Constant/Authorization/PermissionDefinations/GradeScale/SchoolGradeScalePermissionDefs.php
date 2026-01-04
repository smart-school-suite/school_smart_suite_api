<?php

namespace App\Constant\Authorization\PermissionDefinations\GradeScale;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ExamPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\GradeScale\SchoolGradeScalePermissions;

class SchoolGradeScalePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolGradeScalePermissions::AUTO_GEN,
                "Auto Generate",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolGradeScalePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolGradeScalePermissions::VIEW,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolGradeScalePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ExamPermissionCategories::SCHOOL_GRADE_SCALE_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolGradeScalePermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
