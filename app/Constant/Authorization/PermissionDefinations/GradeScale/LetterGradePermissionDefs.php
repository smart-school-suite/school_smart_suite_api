<?php

namespace App\Constant\Authorization\PermissionDefinations\GradeScale;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\GradeScale\LetterGradePermissions;

class LetterGradePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                Guards::APP_ADMIN,
                LetterGradePermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                Guards::APP_ADMIN,
                LetterGradePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                Guards::APP_ADMIN,
                LetterGradePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                Guards::APP_ADMIN,
                LetterGradePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::LETTER_GRADE_MANAGER,
                Guards::SCHOOL_ADMIN,
                LetterGradePermissions::VIEW,
                "View",
                ""
            )
        ];
    }
}
