<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class RequestedFreePeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return RequestedFreePeriod::KEY;
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
