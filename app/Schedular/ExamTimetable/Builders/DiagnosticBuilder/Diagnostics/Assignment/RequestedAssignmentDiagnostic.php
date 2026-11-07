<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class RequestedAssignmentDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return RequestedAssignment::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time', 'hall_ids', 'invigilator_ids'];
    }
}
