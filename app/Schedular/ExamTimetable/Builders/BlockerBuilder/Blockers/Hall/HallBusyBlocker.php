<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class HallBusyBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return HallBusy::KEY;
    }

    protected function fields(): array
    {
        return ['hall_id', 'date', 'start_time', 'end_time', 'source', 'course_id'];
    }
}
