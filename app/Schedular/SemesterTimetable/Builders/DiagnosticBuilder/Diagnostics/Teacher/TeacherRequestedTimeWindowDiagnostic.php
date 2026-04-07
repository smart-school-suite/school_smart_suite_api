<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class TeacherRequestedTimeWindowDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return TeacherRequestedTimeSlot::KEY;
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
                "teacher_id" => $constraintFailed["teacher_id"]
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($diagnostic["blockers"])->toArray();
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
