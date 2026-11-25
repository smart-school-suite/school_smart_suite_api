<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Exceptions\AppException;

trait AutoCreationBoolean
{
    private function boolean(mixed $value): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filtered === null) {
            throw new AppException(
                "This automatic creation setting must be on or off.",
                400,
                "Invalid Automatic Creation Value",
                "This automatic creation setting must be on or off.",
                "value"
            );
        }

        return $filtered;
    }
}
