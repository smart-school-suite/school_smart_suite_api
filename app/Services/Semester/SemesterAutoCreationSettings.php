<?php

namespace App\Services\Semester;

use App\Services\AutoCreation\AutoCreationSettings;

/**
 * The full setting keys of automatic semester creation. The settings
 * themselves are read and applied by AutoCreationSettings, shared with
 * automatic academic year creation.
 */
final class SemesterAutoCreationSettings
{
    public const PREFIX = AutoCreationSettings::SEMESTER;

    public const ENABLED = self::PREFIX . AutoCreationSettings::ENABLED;
    public const LEVELS = self::PREFIX . AutoCreationSettings::LEVELS;
    public const ACROSS_LEVELS = self::PREFIX . AutoCreationSettings::ACROSS_LEVELS;
    public const ACROSS_SPECIALTY = self::PREFIX . AutoCreationSettings::ACROSS_SPECIALTY;
    public const ACROSS_DEPARTMENT_LEVELS = self::PREFIX . AutoCreationSettings::ACROSS_DEPARTMENT_LEVELS;
    public const ACROSS_DEPARTMENT_SPECIALTY = self::PREFIX . AutoCreationSettings::ACROSS_DEPARTMENT_SPECIALTY;
    public const REQUIRE_STUDENTS = self::PREFIX . AutoCreationSettings::REQUIRE_STUDENTS;

    public static function forBranch(string $schoolBranchId): AutoCreationSettings
    {
        return AutoCreationSettings::forBranch($schoolBranchId, self::PREFIX);
    }
}
