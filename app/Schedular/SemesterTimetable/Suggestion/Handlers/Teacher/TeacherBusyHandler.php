<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Handlers\Teacher;

use App\Constant\Violation\SemesterTimetable\Teacher\TeacherBusy;
use App\Schedular\SemesterTimetable\Suggestion\Graph\Node;
use App\Schedular\SemesterTimetable\Suggestion\Handlers\Contracts\SuggestionHandler;

class TeacherBusyHandler implements SuggestionHandler
{
    public function supports(string $type): string
    {
        return TeacherBusy::KEY;
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
