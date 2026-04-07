<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseDailyFrequency;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
class CourseDailyFrequencyDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return CourseDailyFrequency::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockerEngine = app(BlockerRegistry::class);
        $diagnosticDTO->constraint_failed = [
            "type" => CourseDailyFrequency::KEY,
            "details" => [
                "day" => $constraintFailed["day"] ?? null,
                "breach" => $constraintFailed["breach"] ?? null,
                "min" => $constraintFailed["min"] ?? null,
                "max" => $constraintFailed["max"] ?? null,
                "course_id" => $constraintFailed["course_id"] ?? null
            ]
        ];
        $diagnosticDTO->blockers = [];
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
