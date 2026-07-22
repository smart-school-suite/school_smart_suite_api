<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

abstract class DiagnosticContext
{
    protected static object $currentSchool;
    protected static string $versionId;
    public static function setSchool(object $school)
    {
        self::$currentSchool = $school;
    }
    public static function setVersion(string $versionId)
    {
        self::$versionId = $versionId;
    }

    public static function getVersion()
    {
        return self::$versionId;
    }
    public static function getSchool()
    {
        return self::$currentSchool;
    }
}
