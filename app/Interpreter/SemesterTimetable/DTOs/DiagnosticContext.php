<?php

namespace App\Interpreter\SemesterTimetable\DTOs;

abstract class DiagnosticContext
{
    protected static $currentSchool;
    public static function setSchool($school)
    {
        self::$currentSchool = $school;
    }
    public static function getSchool()
    {
        return self::$currentSchool;
    }
}
