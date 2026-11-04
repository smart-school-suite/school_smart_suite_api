<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class SessionDurationBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['course_id', 'duration'];
    }
}
