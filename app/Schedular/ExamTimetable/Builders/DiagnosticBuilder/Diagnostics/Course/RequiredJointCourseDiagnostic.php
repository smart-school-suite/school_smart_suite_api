<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class RequiredJointCourseDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return RequiredJointCourseSession::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'date', 'start_time', 'end_time'];
    }
}
