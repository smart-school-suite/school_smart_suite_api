<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Student;

use App\Constant\Violation\ExamTimetable\Student\CandidateBusy;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class CandidateBusyBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return CandidateBusy::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time', 'student_count', 'busy_course_id', 'source', 'busy_periods'];
    }
}
