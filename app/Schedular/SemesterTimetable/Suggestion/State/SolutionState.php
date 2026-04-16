<?php

namespace App\Schedular\SemesterTimetable\Suggestion\State;

use App\Schedular\SemesterTimetable\Suggestion\Support\ConflictDetector;

class SolutionState
{
    protected array $changes = [];

    public function canApply(array $change): bool
    {
        foreach ($this->changes as $existing) {
            if ((new ConflictDetector())->conflicts($existing, $change)) {
                return false;
            }
        }

        return true;
    }

    public function apply(array $change): void
    {
        $this->changes[] = $change;
    }
}
