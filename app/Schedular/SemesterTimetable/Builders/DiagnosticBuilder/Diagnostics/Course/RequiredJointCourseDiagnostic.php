<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\SemesterTimetable\Course\RequiredJointCourse;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core\BlockerRegistry;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Contracts\DiagnosticBuilder;
use App\Schedular\SemesterTimetable\DTO\DiagnosticDTO;
use Illuminate\Support\Facades\Log;

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
        Log::info('Building RequiredJointCourseDiagnostic', ['constraintFailed' => $constraintFailed, 'blockers' => $blockers]); // Debug log
        $diagnosticDTO->constraint_failed = [
            "constraint" => RequiredJointCourse::KEY,
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
