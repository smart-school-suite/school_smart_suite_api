<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as Constraint;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\AbstractDiagnostic;

class InvigilatorRequestedSlotDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return Constraint::KEY;
    }

    protected function fields(): array
    {
        return ['invigilator_id', 'date', 'start_time', 'end_time'];
    }
}
