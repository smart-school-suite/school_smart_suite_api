<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class SessionDurationBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return SessionDuration::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'allowed_duration', 'requested_duration'];
    }
}
