<?php

namespace App\Schedular\ExamTimetable\Solution\Definitions\Course;

use App\Constant\Violation\ExamTimetable\Course\CourseAlreadyScheduled as Violation;
use App\Schedular\ExamTimetable\Solution\Definitions\AbstractDefinition;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Solution\Support\SolutionContext;
use App\Schedular\ExamTimetable\Solution\Support\Text;

class CourseAlreadyScheduledDefinition extends AbstractDefinition
{
    public function keys(): array
    {
        return [Violation::KEY];
    }

    protected function name(): string
    {
        return 'Course already scheduled';
    }

    public function reason(Subject $obstacle, ?SolutionContext $context = null): string
    {
        return ucfirst(Text::course($obstacle, $context)) . ' already has its exam on ' . Text::when($obstacle) . ' from an earlier request';
    }
}
