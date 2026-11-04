<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class InvigilatorUnavailableBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['invigilator_id', 'date', 'start_time', 'end_time', 'dates', 'reason'];
    }
}
