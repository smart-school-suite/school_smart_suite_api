<?php

namespace App\Schedular\SemesterTimetable\Suggestion\DTO;

abstract class SuggestionContext
{
    protected static array $requestPayload;
    protected static array $timetableGrid;
    protected static array $diagnostics;
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
}
