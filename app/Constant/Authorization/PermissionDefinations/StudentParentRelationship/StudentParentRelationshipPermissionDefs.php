<?php

namespace App\Constant\Authorization\PermissionDefinations\StudentParentRelationship;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\StudentParentRelationship\StudentParentRelationshipPermissions;

class StudentParentRelationshipPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::APP_ADMIN,
                StudentParentRelationshipPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::STUDENT_PARENT_RELATIONSHIP_MANAGER,
                Guards::SCHOOL_ADMIN,
                StudentParentRelationshipPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
