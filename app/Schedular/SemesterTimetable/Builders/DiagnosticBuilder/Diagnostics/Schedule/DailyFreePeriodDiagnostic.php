<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\ScheduleDailyFreePeriod;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class DailyFreePeriodDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return ScheduleDailyFreePeriod::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockerEngine = app(BlockerRegistry::class);
        $diagnosticDTO->constraint_failed = [
            "type" => self::type(),
            "details" => array_filter([
                "day" => $constraintFailed["day"] ?? null,
                "breach" => $constraintFailed["breach"] ?? null,
                "min" => $constraintFailed["min"] ?? null,
                "max" => $constraintFailed["max"] ?? null,
            ])
        ];
        $diagnosticDTO->blockers = [];
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
