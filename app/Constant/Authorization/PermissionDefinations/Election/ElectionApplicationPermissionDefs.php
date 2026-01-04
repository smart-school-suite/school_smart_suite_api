<?php

namespace App\Constant\Authorization\PermissionDefinations\Election;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\ElectionPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Election\ElectionApplicationPermissions;

class ElectionApplicationPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::STUDENT,
                ElectionApplicationPermissions::CREATE,
                "Apply",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionApplicationPermissions::REJECT,
                "Reject",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionApplicationPermissions::APPROVE,
                "Approve",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::STUDENT,
                ElectionApplicationPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionApplicationPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::STUDENT,
                ElectionApplicationPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                ElectionPermissionCategories::ELECTION_APPLICATION_MANAGER,
                Guards::SCHOOL_ADMIN,
                ElectionApplicationPermissions::DELETE,
                "Delete",
                ""
            ),
        ];
    }
}
