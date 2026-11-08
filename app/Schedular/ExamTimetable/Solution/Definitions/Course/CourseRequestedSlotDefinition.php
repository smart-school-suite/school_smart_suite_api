<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest as Constraint;
use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class CourseRequestedSlotDefinition extends AbstractRequestDefinition
{
    public function keys(): array
    {
        return [Constraint::KEY, Violation::KEY];
    }

    protected function name(): string
    {
        return 'Requested exam time';
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $courseId = $target->get('course_id');
        return fn(string $date, int $start, int $end) => $courseId && $context->canHost($courseId, $date, $start, $end);
    }

    public function title(Subject $subject, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::course($subject, $context)) . ' could not be examined at its requested time, ' . Text::when($subject);
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::course($obstacle, $context)) . ' is requested at ' . Text::period($obstacle) . ' that day';
    }

    protected function requestKey(): string
    {
        return Constraint::KEY;
    }

    protected function identityFields(): array
    {
        return ['course_id'];
    }
}
