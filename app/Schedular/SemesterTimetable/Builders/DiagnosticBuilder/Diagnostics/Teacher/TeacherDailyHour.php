<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherDailyHours;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
class TeacherDailyHour implements DiagnosticBuilder
{
    public static function type(): string
    {
        return TeacherDailyHours::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockerEngine = app(BlockerRegistry::class);
        $diagnosticDTO->constraint_failed = [
            "type" => TeacherDailyHours::KEY,
            "details" => [
                "day" => $constraintFailed["day"] ?? null,
                "breach" => $constraintFailed["breach"] ?? null,
                "min" => $constraintFailed["min"] ?? null,
                "max" => $constraintFailed["max"] ?? null,
                "teacher_id" => $constraintFailed["teacher_id"] ?? null
            ]
        ];
        $diagnosticDTO->blockers = [];
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
