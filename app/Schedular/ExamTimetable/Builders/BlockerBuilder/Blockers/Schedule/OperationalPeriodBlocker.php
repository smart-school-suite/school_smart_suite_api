<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod as OperationalPeriodViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class OperationalPeriodBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === OperationalPeriodViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => OperationalPeriodConstraint::KEY,
            "entity_type" => $blocker['entity_type'] ?? null,
            'allowed_dates' => $blocker['allowed_dates'] ?? null,
            'allowed_time' => $blocker['allowed_time'] ?? null
        ]));
        $violation->type = OperationalPeriodViolation::KEY;
        $violation->entity = array_filter([
            'type' => $blocker['entity_type'] ?? null,
            'allowed_dates' => $blocker['allowed_dates'] ?? null,
            'allowed_time' => $blocker['allowed_time'] ?? null
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
