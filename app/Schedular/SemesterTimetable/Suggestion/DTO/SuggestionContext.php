<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

abstract class SuggestionContext
{
    protected static array $requestPayload;
    protected static array $timetableGrid;
    protected static array $diagnostics;
    private static bool $usingPreferences = false;
    private static bool $isHardScenario = true;

    public static function setRequestPayload(array $requestPayload)
    {
        self::$requestPayload = $requestPayload;
    }

    public static function setTimetableGrid(array $timetableGrid)
    {
        self::$timetableGrid = $timetableGrid;
    }

    public static function setDiagnostics(array $diagnostics)
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

    public static function setPreferenceMode(bool $usePreferences): void
    {
        self::$usingPreferences = $usePreferences;
    }

    public static function isUsingPreferences(): bool
    {
        return self::$usingPreferences;
    }

    public static function isWithPreference(): bool
    {
        return self::$usingPreferences === true;
    }

    public static function isWithoutPreference(): bool
    {
        return self::$usingPreferences === false;
    }

    public static function setScenarioMode(bool $isHard): void
    {
        self::$isHardScenario = $isHard;
    }

    public static function isHardScenario(): bool
    {
        return self::$isHardScenario === true;
    }

    public static function isSoftScenario(): bool
    {
        return self::$isHardScenario === false;
    }
}
