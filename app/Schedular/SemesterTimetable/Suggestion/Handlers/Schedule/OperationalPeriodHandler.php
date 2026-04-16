<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Schedule;

use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class OperationalPeriodHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return OperationalPeriod::KEY;
    }

    public function isExclusive(): bool
    {
        return true;
    }

    public function generate(Node $node): array
    {
        return [

        ];
    }
}
