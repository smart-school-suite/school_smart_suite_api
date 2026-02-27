<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Assignment;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Courses;
use App\Models\Hall;
use App\Models\Teacher;
class RequestedAssignmentInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }
    public function supports(string $constraint): bool
    {
        return $constraint === 'requested_assignment';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: 'requested_assignment',
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? [])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        $course = Courses::find($details['course_id'] ?? null);
        $hall = Hall::find($details['hall_id'] ?? null);
        $teacher = Teacher::find($details['teacher_id'] ?? null);
        $hallName = $hall ? $hall->name : 'Unknown Hall';
        $teacherName = $teacher ? $teacher->name : 'Unknown Teacher';
        $courseName = $course ? $course->course_title : 'Unknown Course';
        return "
         The Schedular was unable to schedule the requested assignment: {$courseName}
         from {$details['start_time']} to {$details['end_time']} on {$details['day']} by {$teacherName} in {$hallName}
         as requested. The reasons why this happened are listed below";
    }
}
