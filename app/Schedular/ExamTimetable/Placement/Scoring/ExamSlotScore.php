<?php

namespace App\Schedular\ExamTimetable\Placement\Scoring;

class ExamSlotScore
{
    private const STEP_MINUTES = 15;

    public function suggestSlots(object $examGrid, int $duration): array
    {
        $dayStart = $this->toMinutes($examGrid->startTime);
        $dayEnd   = $this->toMinutes($examGrid->endTime);

        $existingIntervals = array_map(
            fn($alloc) => [
                'start' => $this->toMinutes($alloc["start_time"]),
                'end'   => $this->toMinutes($alloc["end_time"]),
            ],
            (array) $examGrid->allocations
        );

        $firstStart = (int) ceil($dayStart / self::STEP_MINUTES) * self::STEP_MINUTES;
        $candidates = [];

        for ($start = $firstStart; $start + $duration <= $dayEnd; $start += self::STEP_MINUTES) {
            $end    = $start + $duration;
            $minGap = empty($existingIntervals)
                ? $dayEnd - $dayStart
                : min(array_map(
                    fn($iv) => $this->gap($start, $end, $iv['start'], $iv['end']),
                    $existingIntervals
                ));

            $candidates[] = [
                'start_time'      => $this->toTime($start),
                'end_time'        => $this->toTime($end),
                'score'           => $minGap,
                'min_gap_minutes' => $minGap,
            ];
        }

        usort($candidates, fn($a, $b) =>
            $b['score'] !== $a['score']
                ? $b['score'] <=> $a['score']
                : strcmp($b['start_time'], $a['start_time'])
        );

        return $candidates;
    }

    private function gap(int $aStart, int $aEnd, int $bStart, int $bEnd): int
    {
        if ($aEnd <= $bStart) return $bStart - $aEnd;
        if ($bEnd <= $aStart) return $aStart - $bEnd;
        return 0;
    }

    private function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time);
        return (int)$h * 60 + (int)$m;
    }

    private function toTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
