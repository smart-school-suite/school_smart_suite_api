<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class HallCapacityBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['hall_id', 'date', 'seats_needed'];
    }
}
