<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Teacher;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Teacher;

class TeacherMaxDailyHourInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === 'teacher_max_daily_hours';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: 'teacher_max_daily_hours',
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? [])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        $teacher  = Teacher::find($details['teacher_id']);
        $teacherName = $teacher ? $teacher->name : 'Unknown Teacher';
        return "
         The Schedular was unable to enforce the maximum daily hour limit of {$details['max_hours']} for teacher {$teacherName} on {$details['day']}. The reasons why
          this happened are listed below";
    }
}
