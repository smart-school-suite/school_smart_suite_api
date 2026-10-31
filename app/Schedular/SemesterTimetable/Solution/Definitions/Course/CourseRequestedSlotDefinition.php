<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as Constraint;
use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\CourseRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\CourseRequestedTimeSlotDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class CourseRequestedSlotDefinition extends AbstractRequestDefinition
{
    protected function name(): string
    {
        return 'Course requested slot';
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return CourseRequestedTimeSlotDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return CourseRequestedTimeSlotBlocker::class;
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $courseId = $target->get('course_id');

        return fn(string $day, int $start, int $end) => $context->anyTeacherFree($day, $start, $end, $courseId)
            && $context->anyHallFree($day, $start, $end);
    }
}
