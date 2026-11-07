<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class OperationalPeriodDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    protected function fields(): array
    {
        return ['date'];
    }
}
