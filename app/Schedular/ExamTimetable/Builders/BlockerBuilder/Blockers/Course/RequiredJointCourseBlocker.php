<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Course;

use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class RequiredJointCourseBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return RequiredJointCourse::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time'];
    }
}
