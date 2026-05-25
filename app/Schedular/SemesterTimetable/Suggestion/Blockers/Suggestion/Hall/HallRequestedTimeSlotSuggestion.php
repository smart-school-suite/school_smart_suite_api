<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class HallRequestedTimeSlotSuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === HallRequestedTimeSlot::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'time',
            type: 'shift',
            reason: HallRequestedTimeSlot::KEY,
            blocker: $blocker
        );
    }
}
