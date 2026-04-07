<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class CourseRequestedTimeSlotDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return CourseRequestedSlot::KEY;
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
                "course_id" => $constraintFailed["course_id"]
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($diagnostic["blockers"])->toArray();
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
