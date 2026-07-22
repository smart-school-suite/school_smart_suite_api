<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Blockers\Schedule;

use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration as SessionDurationConstraint;
use App\Constant\Violation\ExamTimetable\Schedule\SessionDuration as SessionDurationViolation;
use App\Schedular\ExamTimetable\Builders\BlockerBuilder\Contracts\BlockerBuilder;
use App\Schedular\ExamTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Helpers\GenerateId;
use Override;

class SessionDurationBlocker implements BlockerBuilder
{
    #[Override]
    public function supports(string $type): bool
    {
        return $type === SessionDurationViolation::KEY;
    }

    #[Override]
    public function build(array $blocker): BlockerDTO
    {
        $violation = new BlockerDTO();
        $violation->id = app(GenerateId::class)->generateId(array_filter([
            "type" => SessionDurationConstraint::KEY,
            "entity_type" => $blocker['entity_type'] ?? null,
            'allowed_duration' => $blocker['allowed_duration'] ?? null
        ]));
        $violation->type = SessionDurationViolation::KEY;
        $violation->entity = array_filter([
            "type" => SessionDurationConstraint::KEY,
            'allowed_duration' => $blocker['allowed_duration'] ?? null
        ]);
        $violation->conflict = array_filter([...$blocker['conflict']]);
        return $violation;
    }
}
