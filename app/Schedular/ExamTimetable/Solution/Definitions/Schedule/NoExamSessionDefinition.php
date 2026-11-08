<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\NoExamSession as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class NoExamSessionDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'No exam at that time';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return 'No exam takes place on ' . Text::when($obstacle);
    }
}
