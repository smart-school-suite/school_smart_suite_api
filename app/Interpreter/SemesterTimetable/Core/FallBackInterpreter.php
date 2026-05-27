<?php

namespace App\Interpreter\SemesterTimetable\Core;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
class FallBackInterpreter implements ConstraintInterpreter
{
    public function supports(string $constraint): bool
    {
        return true;
    }

    public function interpret(DiagnosticDTO $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: "A scheduling rule was violated.",
            constraint: $diagnostic->constraint_failed['details'] ?? null,
            severity: "hard",
        );
    }

    private function interpretReasons(array $diagnostic): array
    {
        return [
            "The constraint '{$diagnostic['constraint_failed']['constraint']}' was violated.",
        ];
    }
}
