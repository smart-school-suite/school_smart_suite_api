<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class CourseRequestedSlotDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time', 'reason'];
    }

    protected function identity(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time'];
    }
}
