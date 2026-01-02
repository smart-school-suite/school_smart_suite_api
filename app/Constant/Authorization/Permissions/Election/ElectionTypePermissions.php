<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionTypePermissions
{
    public const CREATE = "election_type.create";
    public const UPDATE = "election_type.update";
    public const DELETE = "election_type.delete";
    public const VIEW = "election_type.view";
    public const DEACTIVATE = "election_type.deactivate";
    public const ACTIVATE = "election_type.activate";

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
