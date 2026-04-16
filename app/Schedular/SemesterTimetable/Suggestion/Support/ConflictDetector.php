<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Support;

class ConflictDetector
{
    public function conflicts(array $a, array $b): bool
    {
        if (
            $a['target']->id === $b['target']->id &&
            $a['action'] === 'modify' &&
            $b['action'] === 'modify'
        ) {
            return true;
        }

        return false;
    }
}
