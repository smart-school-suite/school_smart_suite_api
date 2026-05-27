<?php

namespace App\Interpreter\SemesterTimetable\Contracts;

use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

interface ConstraintInterpreter
{
    public function supports(string $constraint): bool;

    public function interpret(DiagnosticDTO $diagnostic): InterpretedDiagnostic;
}
