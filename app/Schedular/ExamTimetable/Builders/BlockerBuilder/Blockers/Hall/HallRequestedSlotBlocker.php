<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as HallRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Hall\HallRequestedSlot as HallRequestedSlotViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class HallRequestedSlotBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === HallRequestedSlotViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => HallRequestedSlotConstraint::KEY,
            'hall_id' => $blocker['hall_id'] ?? null,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null
        ]));
        $violation->type =  HallRequestedSlotViolation::KEY;
        $violation->entity = array_filter([
            "type" => HallRequestedSlotConstraint::KEY,
            'course_id'  => $blocker['course_id'] ?? null,
            'date'        => $blocker['date'] ?? null,
            'start_time' => $blocker['start_time'] ?? null,
            'end_time'   => $blocker['end_time'] ?? null,
            'hall_id' => $blocker['hall_id'] ?? null,
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
