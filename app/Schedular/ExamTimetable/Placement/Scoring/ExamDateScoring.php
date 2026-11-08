<?php

namespace App\Schedular\ExamTimetable\Placement\Scoring;

use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;

/**
 * Orders the dates a course's exam can go on.
 *
 *  1. Dates that already have exams but fewer than the daily minimum,
 *     so days are filled up to the minimum before new days are opened.
 *  2. Dates without exams, furthest from the nearest exam day first,
 *     so exams are spread over the whole exam period.
 *  3. Dates below the daily maximum, least busy first.
 *  4. Dates at or over the maximum. Placing there breaks the student
 *     daily load, so they are only used when nothing else works.
 */
class ExamDateScoring
{
    /** @return array<int, array{date: string, overflow: bool}> */
    public function rank(State $state): array
    {
        $context = RequestContext::fromPayload();
        $examDays = array_keys(array_filter($state->dateGrid, fn($day) => !empty($day->allocations)));
        $examDayTimes = array_map('strtotime', $examDays);
        $ranked = [];

        foreach (array_keys($state->dateGrid) as $date) {
            $load = $state->load($date);
            $limits = $context->dailyLoadFor($date);
            $min = $limits['min_sessions'] ?? 0;
            $max = $limits['max_sessions'] ?? PHP_INT_MAX;

            $tier = match (true) {
                $load >= $max => 3,
                $load > 0 && $load < $min => 0,
                $load === 0 => 1,
                default => 2,
            };

            $ranked[] = [
                'date' => $date,
                'overflow' => $tier === 3,
                'sort' => [$tier, $tier === 1 ? 0 : $load, -$this->distance(strtotime($date), $examDayTimes), $date],
            ];
        }

        usort($ranked, fn(array $a, array $b) => $a['sort'] <=> $b['sort']);

        return array_map(fn(array $entry) => ['date' => $entry['date'], 'overflow' => $entry['overflow']], $ranked);
    }

    /** Days to the nearest date that has exams; large when there is none yet. */
    private function distance(int $date, array $examDays): int
    {
        if (empty($examDays)) {
            return 0;
        }

        return (int) min(array_map(fn(int $day) => intdiv(abs($date - $day), 86400), $examDays));
    }
}
