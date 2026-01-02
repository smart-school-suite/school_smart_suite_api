<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionCandidatePermissions
{
    public const VIEW = "election_candidate.view";
    public const DIQUALIFY = "election_candidate.disqualify";
    public static function all(): array
    {
        return [
            self::VIEW,
            self::DIQUALIFY
        ];
    }
}
