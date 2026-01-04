<?php

namespace App\Constant\Authorization\Permissions\StudentParentRelationship;

class StudentParentRelationshipPermissions
{
    public const CREATE = "student_parent_relationship.create";
    public const UPDATE = "student_parent_relationship.update";
    public const DELETE = "student_parent_relationship.delete";
    public const VIEW = "student_parent_relationship.view";
    public const DEACTIVATE = "student_parent_relationship.deactivate";
    public const ACTIVATE = "student_parent_relationship.activate";

    public static function all(): array
    {
        return [
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::VIEW,
            self::DEACTIVATE,
            self::ACTIVATE
        ];
    }
}
