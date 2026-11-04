<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment\RequestedAssignmentBlocker;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment\RequestedAssignmentDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class RequestedAssignmentDefinition extends AbstractRequestDefinition
{
    public function constraintKey(): ?string
    {
        return RequestedAssignment::KEY;
    }

    public function violationKey(): ?string
    {
        return RequestedAssignmentViolation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return RequestedAssignmentDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return RequestedAssignmentBlocker::class;
    }

    protected function name(): string
    {
        return 'Requested exam';
    }

    protected function requestKey(): string
    {
        return RequestedAssignment::KEY;
    }

    protected function identityFields(): array
    {
        return ['course_id', 'hall_id', 'invigilator_id'];
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        if ($subject->get('reason') === 'course_not_in_exam') {
            return Text::course($subject, $context) . ' is not one of the courses of this exam';
        }

        return 'The ' . Text::course($subject, $context) . ' exam could not be placed on ' . Text::when($subject) . ' as requested';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return $obstacle->get('reason') === 'same_course'
            ? 'The ' . Text::course($obstacle, $context) . ' exam is already requested on ' . Text::when($obstacle)
            : 'The requested ' . Text::course($obstacle, $context) . ' exam already takes ' . Text::when($obstacle);
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $courseId = $target->get('course_id');
        $hallId = $target->get('hall_id');
        $invigilatorId = $target->get('invigilator_id');
        $seats = $context->context->candidateCount($courseId);

        return fn(string $date, int $start, int $end) => ($hallId ? $context->hallFits($hallId, $date, $start, $end, $seats, $target) : $context->seatsFree($date, $start, $end) >= $seats)
            && ($invigilatorId ? $context->invigilatorFree($invigilatorId, $date, $start, $end, $target) : $context->anyInvigilatorFree($date, $start, $end));
    }
}
