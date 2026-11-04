<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseExamSession as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class CourseExamSessionDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'duration', 'candidate_count'];
    }

    protected function identity(): array
    {
        return ['course_id'];
    }
}
