<?php

namespace App\Constant\Authorization\Permissions\GradeScale;

class GradeScaleCategoryPermissions
{
    public const CREATE = "grade_scale_category.create";
    public const UPDATE = "grade_scale_category.update";
    public const DELETE = "grade_scale_category.delete";
    public const VIEW = "grade_scale_category.view";
    public const DEACTIVATE = "grade_scale_category.deactivate";
    public const ACTIVATE = "grade_scale_category.activate";
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
