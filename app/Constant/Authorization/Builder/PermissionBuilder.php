<?php

namespace App\Constant\Authorization\Builder;

class PermissionBuilder
{
    public static function make(
        string $key,
        string $label,
        string $group,
        string $description
    ): array {
        return [
            'key'         => $key,
            'label'       => $label,
            'group'       => $group,
            'description' => $description,
        ];
    }
}
