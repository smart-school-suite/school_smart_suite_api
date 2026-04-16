<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class TeacherRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return TeacherRequestedTimeSlot::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function generate(Node $node): array
    {
        return [

        ];
    }
}
