<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\SemesterTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
class OperationalPeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return OperationalPeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = OperationalPeriod::KEY;
        $violation->entity = [
            "type" => OperationalPeriodConstraint::KEY
        ];
        $violation->evidence = [
            "start_time" => $blocker["start_time"] ?? null,
            "end_time" => $blocker["end_time"] ?? null,
            "day" => $blocker["day"] ?? null,
        ];
        $violation->conflict = [];
        return $violation;
    }
}
