<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
class HallRequestedTimeSlotDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return HallRequestedTimeWindow::KEY;
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
                "end_time" => $constraintFailed["end_time"],
                "hall_id" => $constraintFailed["hall_id"]
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($diagnostic["blockers"])->toArray();
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
