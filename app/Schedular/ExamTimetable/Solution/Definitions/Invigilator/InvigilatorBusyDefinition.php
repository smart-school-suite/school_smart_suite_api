<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator\InvigilatorBusyBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractResourceDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;
use Illuminate\Support\Collection;

class InvigilatorBusyDefinition extends AbstractResourceDefinition
{
    public function violationKey(): ?string
    {
        return InvigilatorBusy::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return InvigilatorBusyBlocker::class;
    }

    protected function name(): string
    {
        return 'Invigilator booking';
    }

    protected function field(): string
    {
        return 'invigilator_id';
    }

    protected function resourceLabel(): string
    {
        return 'invigilator';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return Text::invigilator($obstacle, $context) . ' is already invigilating on ' . Text::when($obstacle);
    }

    protected function candidates(Issue $issue, array $window, ?string $current, SolutionContext $context): Collection
    {
        return $context->freeInvigilators($issue->date(), $window[0], $window[1], array_filter([$current]));
    }
}
