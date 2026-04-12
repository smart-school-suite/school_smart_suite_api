<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

class RequiredJointCourseDiagnostic implements DiagnosticBuilder
{
    public static function type(): string
    {
        return RequiredJointCourse::KEY;
    }

    public function build($diagnostic): DiagnosticDTO
    {
        $diagnosticDTO = new DiagnosticDTO();
        $blockerEngine = app(BlockerRegistry::class);
        $constraintFailed = $diagnostic["constraint_failed"];
        $blockers = $diagnostic["blockers"];
        $diagnosticDTO->constraint_failed = [
            "type" => RequiredJointCourse::KEY,
            "id" => app(GenerateId::class)->generateId([
                "type" => RequiredJointCourse::KEY,
                "hall_id" => $constraintFailed["hall_id"],
                "course_id" => $constraintFailed["course_id"],
                "day" => $constraintFailed["day"],
                "start_time" => $constraintFailed["start_time"],
                "end_time" => $constraintFailed["end_time"],
                "teacher_id" => $constraintFailed["teacher_id"],
            ]),
            "details" => [
                "hall_id" => $constraintFailed["hall_id"],
                "course_id" => $constraintFailed["course_id"],
                "day" => $constraintFailed["day"],
                "start_time" => $constraintFailed["start_time"],
                "end_time" => $constraintFailed["end_time"],
                "teacher_id" => $constraintFailed["teacher_id"],
            ]
        ];
        $diagnosticDTO->blockers = $blockerEngine->build($blockers)->toArray();
        return $diagnosticDTO;
    }
}
