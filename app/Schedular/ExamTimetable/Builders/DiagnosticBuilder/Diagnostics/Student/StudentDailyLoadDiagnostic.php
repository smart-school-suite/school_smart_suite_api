<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class StudentDailyLoadDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'breach', 'session_count', 'min_sessions', 'max_sessions'];
    }

    protected function identity(): array
    {
        return ['date', 'breach'];
    }
}
