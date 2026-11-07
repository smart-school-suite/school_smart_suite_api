<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\NoExamSession;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class NoExamSessionBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return NoExamSession::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'start_time', 'end_time'];
    }
}
