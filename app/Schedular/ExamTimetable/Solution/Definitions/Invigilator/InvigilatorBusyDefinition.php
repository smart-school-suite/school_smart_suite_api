<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

class InvigilatorBusyDefinition extends AbstractResourceDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Invigilator busy';
    }

    protected function field(): string
    {
        return 'invigilator_id';
    }

    protected function resourceLabel(): string
    {
        return 'invigilator';
    }

    protected function fixedFor(): array
    {
        return [InvigilatorRequestedSlot::KEY];
    }

    /** Free at that time and not teaching the course. */
    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        $named = $issue->subject->get('invigilator_ids', []);
        $courseId = $issue->subject->get('course_id');

        return $context->freeInvigilators($issue->subject->date(), $window[0], $window[1], $courseId, [...$named, ...array_filter([$current])], $courseId);
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::invigilator($obstacle, $context)) . ' already invigilates '
            . ($obstacle->get('course_id') ? 'the ' . ($context?->courseName($obstacle->get('course_id')) ?? 'another') . ' exam' : 'another exam')
            . ' at ' . Text::period($obstacle);
    }
}
