<?php

namespace App\Constant\Authorization\Permissions\SubscriptionPlan;

class FeaturePermissions
{
    public const CREATE = "subscription_feature.create";
    public const DELETE = "subscription_feature.delete";
    public const VIEW = "subscription_feature.view";
    public const UPDATE = "subscription_feature.update";
    public const DEACTIVATE = "subscription_feature.deactivate";
    public const ACTIVATE = "subscription_feature.activate";

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
