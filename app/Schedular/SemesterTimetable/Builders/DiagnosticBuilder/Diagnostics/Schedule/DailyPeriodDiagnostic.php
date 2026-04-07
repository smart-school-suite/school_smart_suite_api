<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyPeriod;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class DailyPeriodDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return ScheduleDailyPeriod::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockerEngine = app(BlockerRegistry::class);
        $diagnosticDTO->constraint_failed = [
            "type" => ScheduleDailyPeriod::KEY,
            "details" => [
                "day" => $constraintFailed["day"] ?? null,
                "breach" => $constraintFailed["breach"] ?? null,
                "min" => $constraintFailed["min"] ?? null,
                "max" => $constraintFailed["max"] ?? null,
            ]
        ];
        $diagnosticDTO->blockers = [];
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
