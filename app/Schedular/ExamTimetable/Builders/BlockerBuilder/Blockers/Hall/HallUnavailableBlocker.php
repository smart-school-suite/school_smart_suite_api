<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class HallUnavailableBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['hall_id', 'date', 'start_time', 'end_time', 'dates', 'reason'];
    }
}
