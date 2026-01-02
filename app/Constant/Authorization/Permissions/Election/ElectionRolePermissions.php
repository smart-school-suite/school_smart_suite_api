<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionRolePermissions
{
    public const CREATE = "election_role.create";
    public const UPDATE = "election_role.update";
    public const DELETE = "election_role.delete";
    public const VIEW = "election_role.view";
    public const ACTIVATE = "election_role.activate";
    public const DEACTIVATE = "election_role.deactivate";
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
