<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Course;

use App\Constant\Violation\SemesterTimetable\Course\CourseRequestedSlot;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class CourseRequestedTimeSlotSuggestion implements BlockerSuggestion
{

    public function support(string $blockerType): bool
    {
        return $blockerType === CourseRequestedSlot::KEY;
    }

    public function getBlockerSuggestion($blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'time',
            type: 'shift',
            reason: CourseRequestedSlot::KEY,
            blocker: $blocker
        );
    }
}
