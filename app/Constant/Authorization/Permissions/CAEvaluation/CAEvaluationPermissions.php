<?php

namespace App\Constant\Authorization\Permissions\CAEvaluation;

class CAEvaluationPermissions
{
    public const ENTER = "ca_evaluation.enter";
    public const UPDATE  = "ca_evaluation.update";

    public static function all(): array
    {
        return [
            self::ENTER,
            self::UPDATE
        ];
    }
}
