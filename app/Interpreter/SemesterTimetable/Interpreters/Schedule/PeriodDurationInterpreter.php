<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Constraint\SemTimetableConstraint;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class PeriodDurationInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === PeriodDuration::KEY;
    }

    public function interpret(DiagnosticDTO $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: SemTimetableConstraint::where("key", PeriodDuration::KEY)->first(),
            severity: 'hard',
            reasons: $this->baseInterpreter->buildReason($diagnostic->blockers ?? []),
        );
    }

    private function buildSummary(DiagnosticDTO $diagnostic): string
    {
        $details = $diagnostic->constraint_failed["details"] ?? [];
        return "The Schedular was unable to enforce the period duration limit of {$details['duration_minutes']} minutes. The reasons why this happened are listed below";
    }
}
