<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Assignment;

use App\Constant\Violation\ExamTimetable\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\AbstractBlocker;

class RequestedAssignmentBlocker extends AbstractBlocker
{
    public static function type(): string
    {
        return RequestedAssignment::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time', 'hall_ids'];
    }
}
