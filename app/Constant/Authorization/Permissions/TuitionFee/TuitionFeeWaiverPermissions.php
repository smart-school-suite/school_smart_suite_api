<?php

namespace App\Constant\Authorization\Permissions\TuitionFee;

class TuitionFeeWaiverPermissions
{
    public const CREATE = "tuition_fee_waiver.create";
    public const DELETE = "tuition_fee_waiver.delete";
    public const UPDATE = "tuition_fee_waiver.update";
    public const VIEW = "tuition_fee_waiver.view";

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
