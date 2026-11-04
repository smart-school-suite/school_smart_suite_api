<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall\HallBusyBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

class HallBusyDefinition extends AbstractResourceDefinition
{
    public function violationKey(): ?string
    {
        return HallBusy::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return HallBusyBlocker::class;
    }

    protected function name(): string
    {
        return 'Hall booking';
    }

    protected function field(): string
    {
        return 'hall_id';
    }

    protected function resourceLabel(): string
    {
        return 'hall';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::hall($obstacle, $context) . ' is already used on ' . Text::when($obstacle);
    }

    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        $seats = $context->context->candidateCount($issue->subject->get('course_id'));

        return $context->freeHalls($issue->date(), $window[0], $window[1], $seats, array_filter([$current]));
    }
}
