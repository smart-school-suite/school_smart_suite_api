<?php

namespace App\Schedular\ExamTimetable\Placement;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

/** Invigilators and halls that asked to be used during a window, so they are picked first. */
class Preferences
{
    public function invigilators(State $state, string $date, int $start, int $end): array
    {
        return $this->matching($state->context->invigilatorRequestedSlots(), 'invigilator_id', $date, $start, $end);
    }

    public function halls(State $state, string $date, int $start, int $end): array
    {
        return $this->matching($state->context->hallRequestedSlots(), 'hall_id', $date, $start, $end);
    }

    private function matching(array $entries, string $field, string $date, int $start, int $end): array
    {
        return collect($entries)
            ->filter(fn($entry) => $entry['date'] === $date && !empty($entry[$field]) && Time::overlaps($start, $end, $entry['start'], $entry['end']))
            ->pluck($field)
            ->unique()
            ->values()
            ->all();
    }
}
