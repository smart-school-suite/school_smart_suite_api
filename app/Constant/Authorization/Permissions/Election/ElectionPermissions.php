<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionPermissions
{
    public const CREATE = "election.create";
    public const DELETE = "election.delete";
    public const VIEW = "election.view";
    public const UPDATE = "election.update";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
        ];
    }
}
