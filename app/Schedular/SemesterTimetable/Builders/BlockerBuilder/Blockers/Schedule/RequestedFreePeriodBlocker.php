<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlockerConstant;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraintConstant;
use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

class RequestedFreePeriodBlocker implements BlockerBuilder
{
    public static function type(): string
    {
        return RequestedFreePeriodBlockerConstant::KEY;
    }

    public function build($blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->type = RequestedFreePeriodBlockerConstant::KEY;
        $violation->id = app(GenerateId::class)->generateId([
            "type" => RequestedFreePeriodConstraintConstant::KEY,
            'day' => $blocker['day'],
            'start_time' => $blocker['start_time'],
            'end_time' => $blocker['end_time'],
        ]);
        $violation->entity = [
            "type" => RequestedFreePeriodConstraintConstant::KEY,
            'day' => $blocker['day'],
            'start_time' => $blocker['start_time'],
            'end_time' => $blocker['end_time'],
        ];
        $violation->evidence = [
            'day' => $blocker['day'],
            'start_time' => $blocker['start_time'],
            'end_time' => $blocker['end_time'],
        ];
        $violation->conflict = array_filter([
            "type" => $blocker["conflict"]["slot_type"] ?? null,
            "start_time" => $blocker["conflict"]["start_time"] ?? null,
            "end_time" => $blocker["conflict"]["end_time"] ?? null,
            "day" => $blocker["conflict"]["day"] ?? null,
            "course_id" => $blocker["conflict"]["course_id"] ?? null,
            "teacher_id" => $blocker["conflict"]["teacher_id"] ?? null,
            "hall_id" => $blocker["conflict"]["hall_id"] ?? null,
        ]);
        return $violation;
    }
}
