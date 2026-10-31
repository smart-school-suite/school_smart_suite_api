<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as Constraint;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Teacher\TeacherRequestedTimeWindowDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

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

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return Text::teacher($subject, $context) . "'s requested time on " . Text::when($subject) . " can't be honoured";
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::teacher($obstacle, $context) . ' has asked to teach at ' . Text::period($obstacle);
    }
}
