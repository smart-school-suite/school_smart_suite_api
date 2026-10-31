<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherDailyHours as Constraint;
use App\Constant\Violation\SemesterTimetable\Teacher\TeacherDailyHours as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Teacher\TeacherDailyHourBlocker;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractLimitDefinition;

class TeacherDailyHoursDefinition extends AbstractLimitDefinition
{
    protected function name(): string
    {
        return 'Teacher daily hours';
    }

    protected function unit(): string
    {
        return 'teaching hours for this teacher per day';
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
        return TeacherDailyHourBlocker::class;
    }
}
