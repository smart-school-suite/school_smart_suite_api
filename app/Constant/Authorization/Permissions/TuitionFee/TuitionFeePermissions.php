<?php

namespace App\Constant\Authorization\Permissions\TuitionFee;

class TuitionFeePermissions
{
    public const VIEW_DEBTOR = "tuition_fee.view_debtors";
    public const DELETE =  "tuition_fee.delete";

    public static function all(): array
    {
        return  [
            self::VIEW_DEBTOR,
            self::DELETE
        ];
    }
}
