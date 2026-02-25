<?php

namespace App\Interpreter\SemesterTimetable\Violation\Contracts;

interface ViolationInterpreter
{
    public static function type(): string;
    public function explain(array $blocker): string;
}
