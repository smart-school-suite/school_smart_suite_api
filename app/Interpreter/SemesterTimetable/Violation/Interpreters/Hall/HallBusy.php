<?php

namespace App\Interpreter\SemesterTimetable\Violation\Interpreters\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy as HallBusyConstant;
use App\Interpreter\SemesterTimetable\Violation\Contracts\ViolationInterpreter;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;

class HallBusy implements ViolationInterpreter
{
    public static function type(): string
    {
        return HallBusyConstant::KEY;
    }

    public function explain(BlockerDTO $blocker): string
    {
        $entity = $blocker->entity ?? null;
        $conflict = $blocker->conflict ?? null;
        $evidence = $blocker->evidence ?? null;
        return "Hall Busy Violation: ";
    }
}
