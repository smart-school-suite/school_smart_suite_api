<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class HallRequestedTimeSlotHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return HallRequestedTimeSlot::KEY;
    }

    public function isExclusive(): bool
    {
        return false;
    }

    public function generate(Node $node): array
    {
        return [];
    }
}
