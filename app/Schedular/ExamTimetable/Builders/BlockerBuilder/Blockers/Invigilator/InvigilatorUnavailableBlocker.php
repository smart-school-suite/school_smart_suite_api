<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class InvigilatorUnavailableBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return InvigilatorUnavailable::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'start_time', 'end_time', 'course_id', 'invigilators_needed', 'invigilators_free', 'teaching_invigilator_ids'];
    }
}
