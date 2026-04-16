<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Hall;

use App\Constant\Violation\SemesterTimetable\Hall\HallBusy;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class HallBusyHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return HallBusy::KEY;
    }
    public function isExclusive(): bool
    {
        return true;
    }

    public function generate(Node $node): array
    {
        return [];
    }
}
