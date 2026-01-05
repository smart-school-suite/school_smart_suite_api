<?php

namespace App\Constant\Authorization\Builder;

class RoleBuilder
{
    public static function make(
        $roleName,
        $descName,
        $roleDesc,
        $guard
    ): array {
        return [
            "role_name" => $roleName,
            "desc_name" => $descName,
            "role_desc" => $roleDesc,
            "guard" => $guard
        ];
    }
}
