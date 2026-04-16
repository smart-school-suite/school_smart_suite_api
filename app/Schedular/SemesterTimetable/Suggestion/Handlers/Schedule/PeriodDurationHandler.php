<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class PeriodDurationHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return PeriodDuration::KEY;
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
