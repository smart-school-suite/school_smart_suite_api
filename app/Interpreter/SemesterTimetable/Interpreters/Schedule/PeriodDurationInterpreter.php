<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Schedule;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
class PeriodDurationInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === 'schedule_period_duration_minutes';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: 'schedule_period_duration_minutes',
            severity: 'hard',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? [])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        return "
         The Schedular was unable to enforce the period duration limit of {$details['duration_minutes']} minutes. The reasons why
          this happened are listed below";
    }
}
