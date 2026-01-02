<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionResultPermissions
{
    public const VIEW = "election_results.view";
    public static function all(): array
    {
        return [
            self::VIEW
        ];
    }
}
