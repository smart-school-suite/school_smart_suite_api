<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Courses;

class CourseMaxDailyFrequencyInterpreter implements ConstraintInterpreter
{

    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }
    public function supports(string $constraint): bool
    {
        return $constraint === 'course_max_daily_frequency';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: 'course_max_daily_frequency',
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? [])
        );
    }
    private function buildSummary(array $diagnostic): string {
         $details = $diagnostic["constraint_failed"]["details"] ?? [];
         $course = Courses::find($details['course_id'] ?? null);
         $courseName = $course ? $course->course_title : 'Unknown Course';
         return "The scheduler has flagged {$courseName} because it appears more than the allowed {$details['max_daily_frequency']} session per day, The reasons why this happened are listed below";
    }
}
