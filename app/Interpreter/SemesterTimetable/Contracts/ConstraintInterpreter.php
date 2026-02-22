<?php

namespace App\Interpreter\SemesterTimetable\Contracts;

use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;

interface ConstraintInterpreter
{
    public function supports(string $constraint): bool;

    public function interpret(array $diagnostic): InterpretedDiagnostic;
}
