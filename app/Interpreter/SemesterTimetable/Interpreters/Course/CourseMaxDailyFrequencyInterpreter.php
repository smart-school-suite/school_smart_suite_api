<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;

class CourseMaxDailyFrequencyInterpreter implements ConstraintInterpreter
{
    public function supports(string $constraint): bool
    {
        throw new \Exception('Not implemented');
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        throw new \Exception('Not implemented');
    }

    private function interpretBlockers(array $diagnostic)
    {

    }
}
