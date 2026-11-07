<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\ExamTimetable\Course\CourseAlreadyScheduled;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class CourseAlreadyScheduledBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return CourseAlreadyScheduled::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time', 'source'];
    }
}
