<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use App\Models\Constraint\SemTimetableConstraint;
use Illuminate\Support\Facades\DB;

class CourseDailyFrequencyInterpreter implements ConstraintInterpreter
{

    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }
    public function supports(string $constraint): bool
    {
        return $constraint === CourseDailyFrequency::KEY;
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: SemTimetableConstraint::where("key", CourseDailyFrequency::KEY)->first(),
            severity: 'soft',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? []),
            suggestions: $this->baseInterpreter->buildSuggestion($diagnostic['suggestions'] ?? [])
        );
    }
    private function buildSummary(array $diagnostic): string {
         $details = $diagnostic["constraint_failed"]["details"] ?? [];
         $course = DB::table('courses')->where("id", $details['course_id'] ?? null)->first();
         $courseName = $course ? $course->course_title : 'Unknown Course';
         return "The scheduler has flagged {$courseName} because it appears more than the allowed {$details['max_daily_frequency']} session per day, The reasons why this happened are listed below";
    }
}
