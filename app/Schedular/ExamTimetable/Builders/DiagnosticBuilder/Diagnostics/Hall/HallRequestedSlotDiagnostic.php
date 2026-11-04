<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class HallRequestedSlotDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['hall_id', 'date', 'start_time', 'end_time'];
    }
}
