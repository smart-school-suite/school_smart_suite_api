<?php

namespace App\Constant\Authorization\Permissions\TuitionFee;

class TuitionFeeScheduleSlotPermissions
{
    public const CREATE = "tution_fee_schedule_slot.create";
    public const DELETE = "tution_fee_schedule_slot.delete";
    public const UPDATE = "tution_fee_schedule_slot.update";
    public const VIEW = "tution_fee_schedule_slot.view";

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
