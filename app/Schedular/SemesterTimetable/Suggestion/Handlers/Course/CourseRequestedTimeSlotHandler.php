<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class CourseRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return CourseRequestedSlot::KEY;
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
