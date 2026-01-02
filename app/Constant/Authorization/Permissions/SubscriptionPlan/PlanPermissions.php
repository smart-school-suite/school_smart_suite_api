<?php

namespace App\Constant\Authorization\Permissions\SubscriptionPlan;

class PlanPermissions
{
    public const CREATE = "subscription_plan.create";
    public const DELETE = "subscription_plan.delete";
    public const VIEW = "subscription_plan.view";
    public const UPDATE = "subscription_plan.update";
    public const DEACTIVATE = "subscription_plan.deactivate";
    public const ACTIVATE = "subscription_plan.activate";

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
