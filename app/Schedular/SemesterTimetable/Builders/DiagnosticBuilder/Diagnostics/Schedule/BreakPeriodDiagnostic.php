<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

class BreakPeriodDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return BreakPeriod::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $blockerEngine = app(BlockerRegistry::class);
        $constraintFailed = $diagnostic["constraint_failed"];
        $diagnosticDTO->constraint_failed = [
            "type" => BreakPeriod::KEY,
            "id" => app(GenerateId::class)->generateId([
                "type" => BreakPeriod::KEY,
                "day" => $constraintFailed["day"],
                "start_time" => $constraintFailed["start_time"],
                "end_time" => $constraintFailed["end_time"]
            ]),
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
