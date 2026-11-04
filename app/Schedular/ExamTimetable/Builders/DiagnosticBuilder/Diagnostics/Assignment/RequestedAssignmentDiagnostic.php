<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class RequestedAssignmentDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'hall_id', 'invigilator_id', 'date', 'start_time', 'end_time', 'reason'];
    }

    protected function identity(): array
    {
        return ['course_id', 'hall_id', 'invigilator_id', 'date', 'start_time', 'end_time'];
    }
}
