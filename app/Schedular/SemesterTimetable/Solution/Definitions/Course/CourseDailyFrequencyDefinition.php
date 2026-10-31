<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency as Constraint;
use App\Constant\Violation\SemesterTimetable\Course\CourseDailyFrequency as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Course\CourseDailyFrequencyBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractLimitDefinition;

class CourseDailyFrequencyDefinition extends AbstractLimitDefinition
{
    protected function name(): string
    {
        return 'Course daily frequency';
    }

    protected function unit(): string
    {
        return 'sessions of this course per day';
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function blockerBuilder(): ?string
    {
        return CourseDailyFrequencyBlocker::class;
    }
}
