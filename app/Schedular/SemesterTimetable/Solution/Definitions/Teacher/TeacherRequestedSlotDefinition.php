<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as Constraint;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Teacher\TeacherRequestedTimeWindowDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class TeacherRequestedSlotDefinition extends AbstractRequestDefinition
{
    protected function name(): string
    {
        return 'Teacher requested slot';
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
        return TeacherRequestedTimeWindowDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return TeacherRequestedTimeSlotBlocker::class;
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $teacherId = $target->get('teacher_id');

        return fn(string $day, int $start, int $end) => ($teacherId ? $context->teacherFree($teacherId, $day, $start, $end) : $context->anyTeacherFree($day, $start, $end))
            && $context->anyHallFree($day, $start, $end);
    }
}
