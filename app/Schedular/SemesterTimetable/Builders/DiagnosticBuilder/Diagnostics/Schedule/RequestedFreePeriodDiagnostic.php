<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
class RequestedFreePeriodDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return RequestedFreePeriod::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockerEngine = app(BlockerRegistry::class);
        $diagnosticDTO->constraint_failed = [
            "type" => self::type(),
            "details" => [
                "day" => $constraintFailed["day"],
                "start_time" => $constraintFailed["start_time"],
                "end_time" => $constraintFailed["end_time"]
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($diagnostic["blockers"])->toArray();
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
