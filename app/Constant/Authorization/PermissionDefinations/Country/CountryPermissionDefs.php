<?php

namespace App\Constant\Authorization\PermissionDefinations\Country;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\Country\CountryPermissions;

class CountryPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::DEACTIVATE,
                "Deactivate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::ACTIVATE,
                "Activate",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::COUNTRY_MANAGER,
                Guards::APP_ADMIN,
                CountryPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
