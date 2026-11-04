<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course\CourseRequestedSlotBlocker;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\CourseRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class CourseRequestedSlotDefinition extends AbstractRequestDefinition
{
    public function constraintKey(): ?string
    {
        return CourseTimeRequest::KEY;
    }

    public function violationKey(): ?string
    {
        return CourseRequestedSlot::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return CourseRequestedSlotDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return CourseRequestedSlotBlocker::class;
    }

    protected function name(): string
    {
        return 'Course time request';
    }

    protected function requestKey(): string
    {
        return CourseTimeRequest::KEY;
    }

    protected function identityFields(): array
    {
        return ['course_id'];
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        if ($subject->get('reason') === 'course_not_in_exam') {
            return Text::course($subject, $context) . ' is not one of the courses of this exam';
        }

        return 'The ' . Text::course($subject, $context) . ' exam could not be held on ' . Text::when($subject) . ' as requested';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return $obstacle->get('reason') === 'same_course'
            ? 'A time is already requested for the ' . Text::course($obstacle, $context) . ' exam on ' . Text::when($obstacle)
            : 'The ' . Text::course($obstacle, $context) . ' exam is requested at ' . Text::when($obstacle);
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $seats = $context->context->candidateCount($target->get('course_id'));

        return fn(string $date, int $start, int $end) => $context->seatsFree($date, $start, $end) >= $seats
            && $context->anyInvigilatorFree($date, $start, $end);
    }
}
