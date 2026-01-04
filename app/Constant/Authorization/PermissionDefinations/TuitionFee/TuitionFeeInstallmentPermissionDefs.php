<?php


namespace App\Constant\Authorization\PermissionDefinations\TuitionFee;

use App\Constant\Authorization\PermissionCategory\PermissionCategoryKeys\SystemPermissionCategories;
use App\Constant\System\Guards;
use App\Constant\Authorization\Builder\PermissionBuilder;
use App\Constant\Authorization\Permissions\TuitionFee\TuitionFeeInstallmentPermissions;

class TuitionFeeInstallmentPermissionDefs
{
    public static function all(): array
    {
        return [
            PermissionBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                Guards::APP_ADMIN,
                TuitionFeeInstallmentPermissions::CREATE,
                "Create",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                Guards::APP_ADMIN,
                TuitionFeeInstallmentPermissions::DELETE,
                "Delete",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                Guards::APP_ADMIN,
                TuitionFeeInstallmentPermissions::UPDATE,
                "Update",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                Guards::APP_ADMIN,
                TuitionFeeInstallmentPermissions::VIEW,
                "View",
                ""
            ),
            PermissionBuilder::make(
                SystemPermissionCategories::TUITION_FEE_INSTALLMENT_MANAGER,
                Guards::SCHOOL_ADMIN,
                TuitionFeeInstallmentPermissions::VIEW,
                "View",
                ""
            ),
        ];
    }
}
