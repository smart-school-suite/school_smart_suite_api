<?php

namespace App\Constant\Authorization\PermissionDefinations\Teacher;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\TeacherPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Teacher\TeacherTimePereferencePermissions;

class TeacherTimePreferencePermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                Guards::SCHOOL_ADMIN,
                TeacherTimePereferencePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                Guards::TEACHER,
                TeacherTimePereferencePermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                Guards::TEACHER,
                TeacherTimePereferencePermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                Guards::TEACHER,
                TeacherTimePereferencePermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                TeacherPermissionCategories::TEACHER_TIME_PREFERENCE_MANAGER,
                Guards::TEACHER,
                TeacherTimePereferencePermissions::ADD,
                "Add",
                ""
            ),
        ];
    }
}
