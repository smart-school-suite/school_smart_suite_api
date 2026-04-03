<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class CourseDailyFrequencyDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return "course_daily_frequency";
    }

    public function build($blocker): DiagnosticDTO {
        $diagnostic = new DiagnosticDTO();
        $diagnostic->constraint_failed = [
            "type" => "course_daily_frequency",
            "details" => [
                "course_id" => $blocker["course_id"],
                "frequency" => $blocker["frequency"],
                "day" => $blocker["day"],
            ]
        ];
        $diagnostic->blockers = [
            [
                "course_id" => $blocker["course_id"],
                "period_id" => $blocker["period_id"],
                "day" => $blocker["day"],
            ],
        ];
        $diagnostic->suggestions = [];
        return $diagnostic;
    }
}
