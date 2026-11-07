<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course;

use App\Constant\Constraint\ExamTimetable\Course\CoursePlacement;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Contracts\AbstractDiagnostic;

class CoursePlacementDiagnostic extends AbstractDiagnostic
{
    public static function type(): string
    {
        return CoursePlacement::KEY;
    }

    protected function fields(): array
    {
        return ['course_id', 'duration', 'candidate_count'];
    }
}
