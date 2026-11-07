<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Core;

use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Assignment\RequestedAssignmentDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\CoursePlacementDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\CourseRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Course\RequiredJointCourseDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Hall\HallRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Invigilator\InvigilatorRequestedSlotDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\OperationalPeriodDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Schedule\SessionDurationDiagnostic;
use App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Diagnostics\Student\StudentDailyLoadDiagnostic;
use Illuminate\Support\Collection;

class DiagnosticRegistry
{
    protected array $builders = [
        RequestedAssignmentDiagnostic::class,
        CoursePlacementDiagnostic::class,
        CourseRequestedSlotDiagnostic::class,
        RequiredJointCourseDiagnostic::class,
        HallRequestedSlotDiagnostic::class,
        InvigilatorRequestedSlotDiagnostic::class,
        OperationalPeriodDiagnostic::class,
        SessionDurationDiagnostic::class,
        StudentDailyLoadDiagnostic::class,
    ];

    public function map(): array
    {
        return collect($this->builders)->mapWithKeys(fn(string $builder) => [$builder::type() => $builder])->all();
    }

    public function build(array $violations): Collection
    {
        $map = $this->map();

        return collect($violations)
            ->filter(fn($violation) => isset($map[$violation['constraint_failed']['key'] ?? null]))
            ->map(fn(array $violation) => app($map[$violation['constraint_failed']['key']])->build($violation))
            ->values();
    }
}
