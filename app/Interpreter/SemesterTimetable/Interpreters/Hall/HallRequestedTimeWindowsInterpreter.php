<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Hall;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Hall;
class HallRequestedTimeWindowsInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;
    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === 'hall_requested_time_windows';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: 'hall_requested_time_windows',
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? [])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        $hall = Hall::find($details['hall_id'] ?? null);
        $hallName = $hall ? $hall->name : 'Unknown Hall';
        return "
         The Schedular was unable to schedule a session in {$hallName} at {$details['start_time']}
         to {$details['end_time']} on {$details['day']} as requested. The reasons why
          this happened are listed below";
    }
}

