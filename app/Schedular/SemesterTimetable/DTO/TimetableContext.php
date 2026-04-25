<?php

namespace App\Schedular\SemesterTimetable\DTO;

abstract class TimetableContext
{
    // A single source of truth
    private static bool $usingPreferences = false;

    public static function setPreferenceMode(bool $usePreferences): void
    {
        self::$usingPreferences = $usePreferences;
    }

    public static function isUsingPreferences(): bool
    {
        return self::$usingPreferences;
    }

    // Helper methods to keep your existing API compatibility
    public static function isWithPreference(): bool
    {
        return self::$usingPreferences === true;
    }

    public static function isWithoutPreference(): bool
    {
        return self::$usingPreferences === false;
    }
}
