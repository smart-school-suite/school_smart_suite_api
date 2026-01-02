<?php

namespace App\Constant\Authorization\Permissions\ResitEvaluation;

class ResitEvaluationPermissions
{
    public const ENTER = "resit_evaluation.enter";
    public const UPDATE  = "resit_evaluation.update";

    public static function all(): array
    {
        return [
            self::ENTER,
            self::UPDATE
        ];
    }
}
