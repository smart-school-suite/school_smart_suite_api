<?php

namespace App\Interpreter\SemesterTimetable\Interpreters\Course;

use App\Constant\Violation\SemesterTimetable\Builder\ViolationBuilder;
use App\Interpreter\SemesterTimetable\Contracts\ConstraintInterpreter;
use App\Interpreter\SemesterTimetable\DTOs\InterpretedDiagnostic;
use App\Interpreter\SemesterTimetable\DTOs\Reason;
use App\Interpreter\SemesterTimetable\Interpreters\Shared\BasedInterpreter;
use App\Interpreter\SemesterTimetable\Violation\Core\ViolationRegistry;
use App\Models\Courses;

class CourseMaxDailyFrequencyInterpreter implements ConstraintInterpreter
{
    private ViolationRegistry $violationRegistry;
    private BasedInterpreter $basedInterpreter;

    public function __construct(ViolationRegistry $violationRegistry, BasedInterpreter $basedInterpreter)
    {
        $this->violationRegistry = $violationRegistry;
        $this->basedInterpreter = $basedInterpreter;
    }
    public function supports(string $constraint): bool
    {
        return $constraint === 'course_max_daily_frequency';
    }

    public function interpret(array $diagnostic): InterpretedDiagnostic
    {
        throw new \Exception('Not implemented');
    }

    private function buildReasons(array $diagnostic)
    {
        $reasons = [];
        foreach ($diagnostic['blockers'] as $blocker) {
            $violation = $this->violationRegistry
                ->resolve($blocker['type']);

            if ($violation) {
                $reasons[] = new Reason(
                    title: ViolationBuilder::title($blocker['type']),
                    description: $violation->explain($blocker)
                );
            }
        }
        return $reasons;
    }
    private function buildSummary(array $diagnostic): string {
         $details = $diagnostic["constraint_failed"]["details"] ?? [];
         $course = Courses::find($details['course_id'] ?? null);
         $courseName = $course ? $course->course_title : 'Unknown Course';
         return "The scheduler has flagged {$courseName} because it appears more than the allowed 1 session per day, The reasons why this happened are listed below";
    }
}
