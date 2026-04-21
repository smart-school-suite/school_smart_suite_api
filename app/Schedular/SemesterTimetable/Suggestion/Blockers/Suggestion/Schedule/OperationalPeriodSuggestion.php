<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class OperationalPeriodSuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === OperationalPeriod::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'time',
            type: 'shift',
            reason: OperationalPeriod::KEY,
            blocker: $blocker
        );
    }
}
