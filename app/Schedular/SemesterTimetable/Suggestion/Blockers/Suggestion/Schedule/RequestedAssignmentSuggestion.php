<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Schedule;

use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class RequestedAssignmentSuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === RequestedAssigment::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'time',
            type: 'shift',
            reason: RequestedAssigment::KEY,
            blocker: $blocker
        );
    }
}
