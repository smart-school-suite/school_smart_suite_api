<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;

class CourseRequestedTimeSlotInterpreter implements ConstraintInterpreter
{
    public function supports(string $constraint): bool
    {
        return $constraint === 'course_requested_time_slot';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        throw new \Exception('Not implemented');
    }
}
