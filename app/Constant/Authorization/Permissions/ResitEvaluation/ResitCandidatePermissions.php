<?php

namespace App\Constant\Authorization\Permissions\ResitEvaluation;

class ResitCandidatePermissions
{
    public const VIEW = "resit_candidate.view";
    public const DELETE = "resit_candidate.delete";
    public const EXEMTED = "resit_candidate.exempted";

    public static function all(): array
    {
        return [
            self::VIEW,
            self::DELETE,
            self::EXEMTED
        ];
    }
}
