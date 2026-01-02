<?php

namespace App\Constant\Authorization\Permissions\SchoolSubscription;

class SchoolSubscriptionTransactionPermissions
{
    /**
     * Create a new class instance.
     */
    public const VIEW = "school_subscription_transaction.view";
    public const DELETE = "school_subscription_transaction.delete";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE
        ];
    }
}
