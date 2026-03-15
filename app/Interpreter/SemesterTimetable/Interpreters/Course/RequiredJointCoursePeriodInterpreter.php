<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BaseInterpreter;
use Illuminate\Support\Facades\DB;

class RequiredJointCoursePeriodInterpreter implements ConstraintInterpreter
{
    private BaseInterpreter $baseInterpreter;

    public function __construct(BaseInterpreter $baseInterpreter)
    {
        $this->baseInterpreter = $baseInterpreter;
    }
    public function supports(string $constraint): bool
    {
        return $constraint === RequiredJointCourse::KEY;
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        return new InterpretedDiagnostic(
            summary: $this->buildSummary($diagnostic),
            constraint: RequiredJointCourse::KEY,
            severity: 'hard',
            reasons: $this->baseInterpreter->buildReason($diagnostic['blockers'] ?? []),
            suggestions: $this->baseInterpreter->buildSuggestion($diagnostic['suggestions' ?? []])
        );
    }

    private function buildSummary(array $diagnostic): string
    {
        $details = $diagnostic["constraint_failed"]["details"] ?? [];
        $course = DB::table('courses')->where("id", $details['course_id'] ?? null)->first();
        $hall = DB::table('halls')->where("id", $details['hall_id'] ?? null)->first();
        $teacher = DB::table("teachers")->where("id", $details['teacher_id'] ?? null)->first();
        $hallName = $hall ? $hall->name : 'Unknown Hall';
        $teacherName = $teacher ? $teacher->name : 'Unknown Teacher';
        $courseName = $course ? $course->course_title : 'Unknown Course';
        return "The scheduler could not schedule the joint course {$courseName} from {$details['start_time']} to {$details['end_time']} on {$details['day']}, taught by {$teacherName} in {$hallName} as requested. The reasons why this happened are listed below";
    }
}
