<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\NoExamInWindow;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule\NoExamInWindowBlocker;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

/** Nothing to change here: the request itself is moved onto a time when an exam runs. */
class NoExamInWindowDefinition extends AbstractDefinition
{
    public function violationKey(): ?string
    {
        return NoExamInWindow::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return NoExamInWindowBlocker::class;
    }

    protected function name(): string
    {
        return 'Free time';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'No exam is scheduled on ' . Text::when($obstacle);
    }
}
