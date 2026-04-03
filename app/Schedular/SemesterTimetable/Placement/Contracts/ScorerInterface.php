<?php

namespace App\Schedular\SemesterTimetable\Placement\Contracts;

use App\Schedular\SemesterTimetable\Placement\Indexes\PlacementIndex;

interface ScorerInterface
{
    /**
     * Score a candidate triple for the given day.
     * Higher score = better candidate.
     * Implementations are responsible for tiebreaking.
     */
    public function score(array $candidate, string $day, PlacementIndex $index): float;
}
