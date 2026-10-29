<?php

namespace App\Schedular\SemesterTimetable\Placement\Scoring;

use App\Schedular\SemesterTimetable\Placement\Contracts\ScorerInterface;
use App\Schedular\SemesterTimetable\Placement\Indexes\PlacementIndex;

class LoadBasedScorer implements ScorerInterface
{
    /**
     * score = -(teacherTotalMinutes + hallTotalMinutes)
     *
     * Totals are running weekly sums that grow with every placement, so the
     * least-loaded teacher and hall win. A random offset below one minute is
     * added so candidates with identical load are picked at random, without
     * ever outweighing a real difference in load.
     */
    public function score(array $candidate, string $day, PlacementIndex $index): float
    {
        $teacherMinutes = $index->teacherTotalMinutes($candidate['teacher_id']);
        $hallMinutes    = $index->hallTotalMinutes($candidate['hall_id']);

        $base = -($teacherMinutes + $hallMinutes);

        return $base + (mt_rand(0, 999) / 1_000);
    }
}
