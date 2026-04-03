<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class BreakPeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return BreakPeriod::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = BreakPeriod::KEY;
        $violation->entity = [
            "type" => ""
        ];
        $violation->evidence = [
            "course" => $blocker["course"],
            "period" => $blocker["period"],
            "day" => $blocker["day"] ?? null,
        ];
        $violation->conflict = [];
        return $violation;
    }
}
