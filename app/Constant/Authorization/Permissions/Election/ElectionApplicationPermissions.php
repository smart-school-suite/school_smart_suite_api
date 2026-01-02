<?php

namespace App\Constant\Authorization\Permissions\Election;

class ElectionApplicationPermissions
{
    public const CREATE = "election_application.create";
    public const VIEW = "election_application.view";
    public const DELETE = "election_application.delete";
    public const APPROVE = "election_application.approve";
    public const REJECT  = "election_application.reject";
    public const UPDATE = "election_application.update";
    public static function all(): array
    {
        return [
            self::CREATE,
            self::DELETE,
            self::UPDATE,
            self::VIEW,
            self::APPROVE,
            self::REJECT
        ];
    }
}
