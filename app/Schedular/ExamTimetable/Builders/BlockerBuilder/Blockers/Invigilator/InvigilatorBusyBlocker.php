<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class InvigilatorBusyBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return InvigilatorBusy::KEY;
    }

    protected function fields(): array
    {
        return ['invigilator_id', 'date', 'start_time', 'end_time', 'source', 'course_id'];
    }
}
