<?php

namespace App\Constant\Authorization\Builder;

class PermissionBuilder
{
    public static function make(
        string $categoryKey,
        string $guard,
        string $name,
        string $desName,
        string $desText
    ): array {
        return [
            'key'         => $categoryKey,
            'guard'       => $guard,
            'name'       => $name,
            'desc_name' => $desName,
            'desc_text' => $desText
        ];
    }
}
