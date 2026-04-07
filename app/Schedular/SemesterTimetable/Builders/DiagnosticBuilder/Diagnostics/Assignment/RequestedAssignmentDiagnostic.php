<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;

class RequestedAssignmentDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return RequestedAssignment::KEY;
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
                "hall_id" => $constraintFailed["hall_id"],
                "course_id" => $constraintFailed["course_id"],
                "teacher_id" => $constraintFailed["teacher_id"]
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($diagnostic["blockers"])->toArray();
        $diagnosticDTO->suggestions = [];
        return $diagnosticDTO;
    }
}
