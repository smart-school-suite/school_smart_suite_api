<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class PeriodDurationDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return PeriodDuration::KEY;
    }

    public function build($diagnostics): DiagnosticDTO
    {
        $diagnostic = new DiagnosticDTO();
        $blockerEngine = app(BlockerRegistry::class);
        $constraintFailed = $diagnostics["constraint_failed"];
        $blockers = $diagnostics["blockers"];
        $diagnostic->constraint_failed = [
            "type" => PeriodDuration::KEY,
            "details" => [
                "day" => $constraintFailed["day"],
                "duration_minutes" => $constraintFailed["duration"],
            ]
        ];
        $diagnostic->blockers = $blockerEngine->build($blockers)->toArray();
        $diagnostic->suggestions = [];
        return $diagnostic;
    }
}
