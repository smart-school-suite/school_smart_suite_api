<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment;

use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment as Violation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\AbstractBlocker;

class RequestedAssignmentBlocker extends AbstractBlocker
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
