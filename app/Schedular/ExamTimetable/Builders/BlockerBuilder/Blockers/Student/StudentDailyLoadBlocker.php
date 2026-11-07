<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student;

use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class StudentDailyLoadBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return StudentDailyLoad::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'breach', 'min_sessions', 'max_sessions', 'sessions', 'course_ids'];
    }
}
