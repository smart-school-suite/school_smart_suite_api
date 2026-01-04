<?php

namespace App\Constant\Authorization\PermissionDefinations\StudentBatch;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\StudentPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\StudentBatch\StudentBatchPermissions;

class StudentBatchPermissionDefs
{
    public static function all(): array
    {
        return  [
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::DEACTIVATE_STUDENT_BATCH,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                StudentPermissionCategories::STUDENT_BATCH_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentBatchPermissions::ACTIVATE_STUDENT_BATCH,
                "Activate",
                ""
            ),
        ];
    }
}
