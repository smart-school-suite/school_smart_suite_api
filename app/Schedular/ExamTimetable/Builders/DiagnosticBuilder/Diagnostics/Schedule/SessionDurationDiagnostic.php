<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class SessionDurationDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return SessionDuration::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'duration'];
    }
}
