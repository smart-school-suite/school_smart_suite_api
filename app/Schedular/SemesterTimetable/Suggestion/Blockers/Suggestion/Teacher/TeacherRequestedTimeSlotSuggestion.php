<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class TeacherRequestedTimeSlotSuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === TeacherRequestedTimeSlot::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'time',
            type: 'shift',
            reason: TeacherRequestedTimeSlot::KEY,
            blocker: $blocker
        );
    }
}
