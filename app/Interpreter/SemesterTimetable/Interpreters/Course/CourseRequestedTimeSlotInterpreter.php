<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Constraint\SemTimetableConstraint;
use App\Models\Courses;
class CourseRequestedTimeSlotInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;
    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }

    public function supports(string $constraint): bool
    {
        return $constraint === CourseRequestedSlot::KEY;
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: SemTimetableConstraint::where("key", CourseRequestedSlot::KEY)->first(),
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? []),
            suggestions: $this->baseInterpreter->buildSuggestion($diagnostic['suggestions'] ?? [])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        $course = Courses::find($details['course_id'] ?? null);
        $courseName = $course ? $course->course_title : 'Unknown Course';
        return "The Schedular was unable to schedule {$courseName} at {$details['start_time']} to {$details['end_time']} on {$details['day']} as requested. The reasons why this happened are listed below";
    }
}
