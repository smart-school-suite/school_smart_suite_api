<?php

namespace App\Constant\Authorization\Builder;

class PermissionCategoryBuilder
{
    public static function make(
        string $key,
        string $name,
        string $description
    ): array {
        return [
            'key'         => $key,
            'name'       => $name,
            'description' => $description,
        ];
    }
}
