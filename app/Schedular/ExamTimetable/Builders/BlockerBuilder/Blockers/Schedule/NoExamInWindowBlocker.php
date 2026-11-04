<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\NoExamInWindow as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class NoExamInWindowBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['date', 'start_time', 'end_time'];
    }
}
