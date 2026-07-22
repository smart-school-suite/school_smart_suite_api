<?php

namespace App\Schedular\ExamTimetable\Placement\Scoring;

use App\Schedular\ExamTimetable\Core\State;

class ExamDateScoring
{
    public function ScoreGridByBusyness(State $state)
    {
        $scores = [];

        foreach ($state->dateGrid as $dateString => $examDateDTO) {
            $scores[] = [
                'date' => $dateString,
                'allocations' => count($examDateDTO->allocations)
            ];
        }

        usort($scores, function ($a, $b) {
            return $a['allocations'] <=> $b['allocations'];
        });

        return $scores;
    }
}
