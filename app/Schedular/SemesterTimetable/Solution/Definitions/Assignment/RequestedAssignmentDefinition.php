<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as Constraint;
use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Assignment\RequestedAssignmentBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment\RequestedAssignmentDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use App\Schedular\SemesterTimetable\Solution\Support\Text;

class RequestedAssignmentDefinition extends AbstractRequestDefinition
{
    protected function name(): string
    {
        return 'Requested assignment';
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
        return RequestedAssignmentDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequestedAssignmentBlocker::class;
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $teacherId = $target->get('teacher_id');
        $hallId = $target->get('hall_id');
        $courseId = $target->get('course_id');

        return fn(string $day, int $start, int $end) => ($teacherId ? $context->teacherFree($teacherId, $day, $start, $end) : $context->anyTeacherFree($day, $start, $end, $courseId))
            && ($hallId ? $context->hallFree($hallId, $day, $start, $end) : $context->anyHallFree($day, $start, $end));
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return Text::course($subject, $context) . ' with ' . Text::teacher($subject, $context, 'the teacher') . ' in ' . Text::hall($subject, $context, 'the hall') . ' can\'t be scheduled on ' . Text::when($subject);
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::course($obstacle, $context) . ' with ' . Text::teacher($obstacle, $context, 'the teacher') . ' in ' . Text::hall($obstacle, $context, 'the hall') . ' needs the same teacher or hall at ' . Text::period($obstacle);
    }
}
