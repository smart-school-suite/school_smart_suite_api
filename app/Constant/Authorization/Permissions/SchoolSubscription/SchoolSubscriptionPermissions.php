<?php

namespace App\Constant\Authorization\Permissions\SchoolSubscription;

class SchoolSubscriptionPermissions
{
    public const RENEW  = "school_subscription.renew";
    public const CANCEL = "school_subscription.cancel";
    public static function all(): array
    {
        return [
            self::RENEW,
            self::CANCEL
        ];
    }
}
