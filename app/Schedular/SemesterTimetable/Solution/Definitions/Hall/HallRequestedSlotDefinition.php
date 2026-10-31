<?php

namespace App\Schedular\SemesterTimetable\Solution\Definitions\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow as Constraint;
use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot as Violation;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Hall\HallRequestedTimeSlotBlocker;
use App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Diagnostics\Hall\HallRequestedTimeSlotDiagnostic;
use App\Schedular\SemesterTimetable\Solution\Definitions\AbstractRequestDefinition;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\Solution\Support\SolutionContext;

class HallRequestedSlotDefinition extends AbstractRequestDefinition
{
    protected function name(): string
    {
        return 'Hall requested slot';
    }

    public function constraintKey(): ?string
    {
        return Constraint::KEY;
    }

    public function violationKey(): ?string
    {
        return Violation::KEY;
    }

    public function diagnosticBuilder(): ?string
    {
        return HallRequestedTimeSlotDiagnostic::class;
    }

    public function blockerBuilder(): ?string
    {
        return HallRequestedTimeSlotBlocker::class;
    }

    protected function accepts(Subject $target, SolutionContext $context): callable
    {
        $hallId = $target->get('hall_id');

        return fn(string $day, int $start, int $end) => ($hallId ? $context->hallFree($hallId, $day, $start, $end) : $context->anyHallFree($day, $start, $end))
            && $context->anyTeacherFree($day, $start, $end);
    }
}
