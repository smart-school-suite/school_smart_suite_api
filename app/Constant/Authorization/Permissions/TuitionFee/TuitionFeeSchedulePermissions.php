<?php

namespace App\Constant\Authorization\Permissions\TuitionFee;

class TuitionFeeSchedulePermissions
{
    public const CREATE = "tuition_fee_schedule.create";
    public const DELETE = "tuition_fee_schedule.delete";
    public const UPDATE = "tuition_fee_schedule.update";
    public const VIEW = "tuition_fee_schedule.view";

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
