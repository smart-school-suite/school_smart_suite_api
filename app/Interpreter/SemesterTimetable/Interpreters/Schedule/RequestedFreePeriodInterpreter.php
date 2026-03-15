<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
class RequestedFreePeriodInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === RequestedFreePeriod::KEY;
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: RequestedFreePeriod::KEY,
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? []),
            suggestions: $this->baseInterpreter->buildSuggestion($diagnostic['suggestions' ?? []])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        return "The Schedular was unable to schedule the requested free period on {$details['day']} from {$details['start_time']} to {$details['end_time']}. The reasons why this happened are listed below";
    }
}
