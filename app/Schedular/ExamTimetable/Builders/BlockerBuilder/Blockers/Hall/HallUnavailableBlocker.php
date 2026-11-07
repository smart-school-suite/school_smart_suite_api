<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class HallUnavailableBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return HallUnavailable::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'start_time', 'end_time', 'seats_needed', 'seats_free', 'busy_hall_ids'];
    }
}
