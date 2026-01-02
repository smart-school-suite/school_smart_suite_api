<?php

namespace App\Constant\Authorization\Permissions\TuitionFee;

class TuitionFeeInstallmentPermissions
{
    public const CREATE = "tuition_fee_installment.create";
    public const DELETE = "tuition_fee_installment.delete";
    public const UPDATE = "tuition_fee_installment.update";
    public const VIEW = "tuition_fee_installment.view";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW
        ];
    }
}
