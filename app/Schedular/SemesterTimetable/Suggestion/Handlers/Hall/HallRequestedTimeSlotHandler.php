<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot as HallRequestedTimeSlotBlocker;
use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow as HallRequestedTimeSlotConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionOptionDTO;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Core\BlockerRegistry;

class HallRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return $type === HallRequestedTimeSlotBlocker::KEY || $type === HallRequestedTimeSlotConstraint::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function allowedActions(): array
    {
        return ["keep", "modify", "remove"];
    }

    public function conflictOptions($constraint): array
    {
        return [
            new SuggestionOptionDTO(
                action: 'remove',
                label: 'Remove Hall Requested Slot'
            ),
            new SuggestionOptionDTO(
                action: 'modify',
                label: 'Move Hall Slot  to another time',
                meta: ['field' => 'time']
            )
        ];
    }

    public function dependencyOptions($constraint, array $blockers): array
    {
        $resolveChanges = app(BlockerRegistry::class)->generateBlockerSuggestions($blockers);
        return $resolveChanges;
    }
}
