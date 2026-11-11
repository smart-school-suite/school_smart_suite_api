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

    public const MODE = self::PREFIX . AutoCreationSettings::MODE;
    public const LEVELS = self::PREFIX . AutoCreationSettings::LEVELS;
    public const ACROSS_SPECIALTIES = self::PREFIX . AutoCreationSettings::ACROSS_SPECIALTIES;
    public const ACROSS_ALL_LEVELS = self::PREFIX . AutoCreationSettings::ACROSS_ALL_LEVELS;
    public const REQUIRE_STUDENTS = self::PREFIX . AutoCreationSettings::REQUIRE_STUDENTS;

    public static function forBranch(string $schoolBranchId): AutoCreationSettings
    {
        return AutoCreationSettings::forBranch($schoolBranchId, self::PREFIX);
    }
}
