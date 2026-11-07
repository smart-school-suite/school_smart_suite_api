<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class HallCapacityBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return HallCapacityExceeded::KEY;
    }

    protected function fields(): array
    {
        return ['hall_ids', 'total_capacity', 'seats_free', 'total_candidates'];
    }
}
