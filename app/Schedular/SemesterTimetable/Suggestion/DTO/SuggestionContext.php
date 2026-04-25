<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

abstract class SuggestionContext
{
    protected static array $requestPayload;
    protected static array $timetableGrid;
    protected static array $diagnostics;
    private static bool $usingPreferences = false;
    public static function setRequestPayload($requestPayload)
    {
        self::$requestPayload = $requestPayload;
    }
    public static function setTimetableGrid($timetableGrid)
    {
        self::$timetableGrid = $timetableGrid;
    }
    public static function setDiagnostics($diagnostics)
    {
        self::$diagnostics = $diagnostics;
    }
    public function getRequestPayload(): array
    {
        return self::$requestPayload;
    }
    public function getTimetableGrid(): array
    {
        return self::$timetableGrid;
    }
    public function getDiagnostics(): array
    {
        return self::$diagnostics;
    }

        // A single source of truth

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
