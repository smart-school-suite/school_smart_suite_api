<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Student;

use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class StudentDailyLoadDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return StudentDailyLoadRange::KEY;
    }

    protected function fields(): array
    {
        return ['date', 'breach', 'min_sessions', 'max_sessions', 'sessions'];
    }
}
