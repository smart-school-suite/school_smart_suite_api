<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Violation\ExamTimetable\Hall\HallBusy as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

class HallBusyDefinition extends AbstractResourceDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Hall busy';
    }

    protected function field(): string
    {
        return 'hall_id';
    }

    protected function resourceLabel(): string
    {
        return 'hall';
    }

    protected function fixedFor(): array
    {
        return [HallRequestedSlot::KEY];
    }

    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        $named = $issue->subject->get('hall_ids', []);
        return $context->freeHalls($issue->subject->date(), $window[0], $window[1], [...$named, ...array_filter([$current])], $issue->subject->get('course_id'));
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::hall($obstacle, $context)) . ' is already used'
            . ($obstacle->get('course_id') ? ' by the ' . ($context?->courseName($obstacle->get('course_id')) ?? 'another') . ' exam' : ' by another exam')
            . ' at ' . Text::period($obstacle);
    }
}
