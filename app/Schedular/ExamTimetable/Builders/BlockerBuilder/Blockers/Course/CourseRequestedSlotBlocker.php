<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\ExamTimetable\Course\CourseRequestedSlot as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class CourseRequestedSlotBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time'];
    }
}
