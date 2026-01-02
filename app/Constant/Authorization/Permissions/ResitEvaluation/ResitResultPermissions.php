<?php

namespace App\Constant\Authorization\Permissions\ResitEvaluation;

class ResitResultPermissions
{
    public const DELETE = "resit_result.delete";
    public const VIEW = "resit_result.view";

    public static function all(): array
    {
        return [
            self::DELETE,
            self::VIEW
        ];
    }
}
