<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationConstraint;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;

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
        $violation->id = app(GenerateId::class)->generateId([
            "type" => PeriodDurationConstraint::KEY,
            "day" => $blocker["day"] ?? null,
            "period_duration" => $blocker["expected_duration"] ?? null,
        ]);
        $violation->entity = [
            "type" => PeriodDurationConstraint::KEY,
            "day" => $blocker["day"] ?? null,
            "period_duration" => $blocker["expected_duration"] ?? null,
        ];
        $violation->conflict = [
            "expected_duration" => $blocker["expected_duration"] ?? null,
            "actual_duration" => $blocker["actual_duration"] ?? null,
            "day" => $blocker["day"] ?? null,
        ];
        $violation->evidence = array_filter([
            "course_id"  => $blocker["conflict"]["course_id"] ?? null,
            "start_time" => $blocker["conflict"]["start_time"] ?? null,
            "end_time"   => $blocker["conflict"]["end_time"] ?? null,
            "day"        => $blocker["conflict"]["day"] ?? null,
            "slot_type"  => $blocker["conflict"]["slot_type"] ?? null,
            "teacher_id" => $blocker["conflict"]["teacher_id"] ?? null,
            "hall_id"    => $blocker["conflict"]["hall_id"] ?? null,
        ]);
        return $violation;
    }
}
