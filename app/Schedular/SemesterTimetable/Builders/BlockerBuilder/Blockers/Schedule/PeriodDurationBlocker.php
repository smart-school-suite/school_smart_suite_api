<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationConstraint;
class PeriodDurationBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return PeriodDuration::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = PeriodDuration::KEY;
        $violation->entity = [
            "type" => PeriodDurationConstraint::KEY
        ];
        $violation->evidence = [
            "expected_duration" => $blocker["expected_duration"] ?? null,
            "actual_duration" => $blocker["actual_duration"] ?? null,
            "day" => $blocker["day"] ?? null,
        ];
        $violation->conflict = [];
        return $violation;
    }
}
