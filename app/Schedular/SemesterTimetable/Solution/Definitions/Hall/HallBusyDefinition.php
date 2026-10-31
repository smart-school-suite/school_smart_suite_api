<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow;
use App\Constant\Violation\SemesterTimetable\Hall\HallBusy as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Hall\HallBusyBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Issue;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;
use Illuminate\Support\Collection;
use App\Schedular\SemesterTimetable\Solution\Support\Text;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;

class HallBusyDefinition extends AbstractResourceDefinition
{
    protected function name(): string
    {
        return 'Hall busy';
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return HallBusyBlocker::class;
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
        return [HallRequestedTimeWindow::KEY];
    }

    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        return $context->freeHalls($issue->day(), $window[0], $window[1], array_filter([$current]));
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::hall($obstacle, $context) . ' is already in use at ' . Text::period($obstacle);
    }
}
