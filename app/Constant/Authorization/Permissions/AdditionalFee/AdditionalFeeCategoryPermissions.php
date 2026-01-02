<?php

namespace App\Constant\Authorization\Permissions\AdditionalFee;

class AdditionalFeeCategoryPermissions
{
    public const CREATE = "additional_fee_category.create";
    public const DELETE = "additional_fee_category.delete";
    public const UPDATE =  "additional_fee_category.update";
    public const VIEW = "additional_fee_category.view";
    public const DEACTIVATE = "additional_fee_category.deactivate";
    public const ACTIVATE = "additional_fee_category.activate";
    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
