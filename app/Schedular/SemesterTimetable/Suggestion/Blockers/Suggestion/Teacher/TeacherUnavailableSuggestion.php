<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Blockers\Suggestion\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherUnavailable;
use App\Schedular\SemesterTimetable\DTO\BlockerDTO;
use App\Schedular\SemesterTimetable\Suggestion\Blockers\Contract\BlockerSuggestion;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ChangeDTO;

class TeacherUnavailableSuggestion implements BlockerSuggestion
{
    public function support(string $blockerType): bool
    {
        return $blockerType === TeacherUnavailable::KEY;
    }

    public function getBlockerSuggestion(BlockerDTO $blocker): ChangeDTO
    {
        return new ChangeDTO(
            field: 'teacher_id',
            type: 'replace',
            reason: TeacherUnavailable::KEY,
            blocker: $blocker
        );
    }
}
