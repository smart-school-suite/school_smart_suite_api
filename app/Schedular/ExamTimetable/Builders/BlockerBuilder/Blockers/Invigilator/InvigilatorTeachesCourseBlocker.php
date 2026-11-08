<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorTeachesCourse;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class InvigilatorTeachesCourseBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return InvigilatorTeachesCourse::KEY;
    }

    protected function fields(): array
    {
        return ['invigilator_id', 'invigilator_ids', 'course_id'];
    }
}
