<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class InvigilatorBusyBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['invigilator_id', 'date', 'start_time', 'end_time'];
    }
}
