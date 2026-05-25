<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class HallBusySuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === HallBusy::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'hall_id',
            type: 'replace',
            reason: HallBusy::KEY,
            blocker: $blocker
        );
    }
}
