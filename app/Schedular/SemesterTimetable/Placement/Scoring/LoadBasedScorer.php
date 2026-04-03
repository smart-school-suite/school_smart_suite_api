<?php

namespace App\Schedular\SemesterTimetable\Placement\Scoring;

use App\Schedular\SemesterTimetable\Placement\Contracts\ScorerInterface;
use App\Schedular\SemesterTimetable\Placement\Indexes\PlacementIndex;

class LoadBasedScorer implements ScorerInterface
{
    /**
     * score = 1/(teacherBusyMinutes + 1) + 1/(hallBusyMinutes + 1)
     *
     * A random micro-offset is added so candidates with identical load
     * scores are picked at random rather than by insertion order.
     */
    public function score(array $candidate, string $day, PlacementIndex $index): float
    {
        $teacherMinutes = $index->teacherBusyMinutesOnDay($candidate['teacher_id'], $day);
        $hallMinutes    = $index->hallBusyMinutesOnDay($candidate['hall_id'], $day);

        $base = (1 / ($teacherMinutes + 1)) + (1 / ($hallMinutes + 1));

        return $base + (mt_rand(0, 999) / 1_000_000);
    }
}
