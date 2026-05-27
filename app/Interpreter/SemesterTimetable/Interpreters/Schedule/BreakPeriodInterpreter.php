<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Constraint\SemTimetableConstraint;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class BreakPeriodInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === BreakPeriod::KEY;
    }

    public function interpret(DiagnosticDTO $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: SemTimetableConstraint::where("key", BreakPeriod::KEY)->first(),
            severity: 'hard',
            reasons: $this->baseInterpreter->buildReason($diagnostic->blockers ?? []),
        );
    }

    private function buildSummary(DiagnosticDTO $diagnostic): string
    {
        $details = $diagnostic->constraint_failed["details"] ?? [];
        return "The Schedular was unable to schedule a break period from {$details['start_time']} to {$details['end_time']} on {$details['day']} as requested. The reasons why this happened are listed below";
    }
}
