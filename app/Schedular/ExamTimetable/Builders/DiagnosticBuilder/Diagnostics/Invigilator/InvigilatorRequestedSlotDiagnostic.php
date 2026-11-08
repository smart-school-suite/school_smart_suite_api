<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class InvigilatorRequestedSlotDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return InvigilatorRequestedSlot::KEY;
    }

    protected function fields(): array
    {
        return ['invigilator_id', 'date', 'start_time', 'end_time'];
    }
}
