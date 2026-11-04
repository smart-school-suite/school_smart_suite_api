<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class RequiredJointCourseDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time'];
    }
}
