<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student;

use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class StudentDailyLoadBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return Violation::KEY;
    }

    protected function identity(): array
    {
        return ['date', 'breach'];
    }
}
