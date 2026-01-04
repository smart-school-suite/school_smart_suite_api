<?php

namespace App\Constant\Authorization\PermissionDefinations\SchoolAdmin;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SchoolPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\SchoolAdmin\SchoolAdminPermissions;

class SchoolAdminPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::ASSIGN_ROLE,
                "Assign Role",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::REVOKE_ROLE,
                "Revoke Role",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::ASSIGN_PERMISSION,
                "Assign Permission",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::REVOKE_PERMISSION,
                "Rovoke Permission",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::CHANGE_PASSWORD,
                "Change Password",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::AVATAR_DELETE,
                "Avatar Delete",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::AVATAR_UPLOAD,
                "Avatar Upload",
                ""
            ),
            PermissionBuilder::make(
                SchoolPermissionCategories::SCHOOL_ADMIN_MANAGER,
                Guards::SCHOOL_ADMIN,
                SchoolAdminPermissions::PROFILE_UPDATE,
                "Update Profile",
                ""
            ),
        ];
    }
}
