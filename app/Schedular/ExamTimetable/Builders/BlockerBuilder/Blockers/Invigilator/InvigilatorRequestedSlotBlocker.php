<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as InvigRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorRequestedSlot as InvigRequestedSlotViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class InvigilatorRequestedSlotBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === InvigRequestedSlotViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => InvigRequestedSlotConstraint::KEY,
            "invigilator_id" => $blocker['invigilator_id'] ?? null,
            'course_id' => $blocker['course_id'] ?? null,
            "date" => $blocker['date'] ?? null,
            "start_time" => $blocker['start_time'] ?? null,
            "end_time" => $blocker['end_time'] ?? null,
        ]));
        $violation->type = InvigRequestedSlotViolation::KEY;
        $violation->entity = array_filter([
            "type" => InvigRequestedSlotConstraint::KEY,
            "invigilator_id" => $blocker['invigilator_id'] ?? null,
            'course_id' => $blocker['course_id'] ?? null,
            "date" => $blocker['date'] ?? null,
            "start_time" => $blocker['start_time'] ?? null,
            "end_time" => $blocker['end_time'] ?? null,
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
