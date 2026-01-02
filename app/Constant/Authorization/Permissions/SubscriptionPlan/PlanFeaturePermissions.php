<?php

namespace App\Constant\Authorization\Permissions\SubscriptionPlan;

class PlanFeaturePermissions
{
    public const ASSIGN = "subscription_feature.create";
    public const REMOVE = "subscription_feature.delete";
    public const VIEW = "subscription_feature.view";

    public static function all(): array
    {
        return [
            self::ASSIGN,
            self::REMOVE,
            self::VIEW
        ];
    }
}
