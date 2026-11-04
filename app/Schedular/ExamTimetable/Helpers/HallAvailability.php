<?php

namespace App\Schedular\ExamTimetable\Helpers;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;

/**
 * Finds hall seats for one exam session.
 *
 * A hall is:
 *  - free    when nothing else uses it during the window,
 *  - shared  when another exam already sits in it for exactly the same window and seats are left
 *            (candidates of different specialties can sit different papers in one hall),
 *  - busy    when anything else overlaps the window in any other way.
 *
 * Allocation prefers, in order: one free hall that fits everyone (smallest that fits), one shared hall
 * that fits everyone, then the fewest halls that together fit everyone (free halls first, largest first).
 */
class HallAvailability
{
    public const FREE = 'free';
    public const SHARED = 'shared';
    public const BUSY = 'busy';

    public function status(State $state, array $hall, string $date, int $start, int $end): array
    {
        $capacity = (int) $hall['capacity'];

        foreach ($state->sessionsOn($date) as $session) {
            if ($session->overlaps($start, $end) && in_array($hall['hall_id'], $session->hallIds(), true)) {
                return ['state' => self::BUSY, 'remaining' => 0, 'invigilators' => [], 'busy_with' => [
                    'course_id' => $session->courseId,
                    'start' => $session->start,
                    'end' => $session->end,
                ]];
            }
        }

        $shared = null;

        foreach ($state->context->hallBusy($hall['hall_id'], $date) as $slot) {
            if (!($slot['start'] < $end && $slot['end'] > $start)) {
                continue;
            }

            if ($slot['start'] !== $start || $slot['end'] !== $end) {
                return ['state' => self::BUSY, 'remaining' => 0, 'invigilators' => [], 'busy_with' => [
                    'start' => $slot['start'],
                    'end' => $slot['end'],
                ]];
            }

            $shared = [
                'candidates' => ($shared['candidates'] ?? 0) + $slot['candidate_count'],
                'invigilators' => array_values(array_unique([...($shared['invigilators'] ?? []), ...$slot['invigilators']])),
            ];
        }

        if ($shared === null) {
            return ['state' => self::FREE, 'remaining' => $capacity, 'invigilators' => []];
        }

        $remaining = max(0, $capacity - $shared['candidates']);

        return [
            'state' => $remaining > 0 ? self::SHARED : self::BUSY,
            'remaining' => $remaining,
            'invigilators' => $shared['invigilators'],
            'busy_with' => $remaining > 0 ? null : ['start' => $start, 'end' => $end, 'full' => true],
        ];
    }

    /** Every hall with its status for the window. */
    public function survey(State $state, string $date, int $start, int $end): array
    {
        return $state->context->halls()
            ->map(fn(array $hall) => [...$hall, ...$this->status($state, $hall, $date, $start, $end)])
            ->values()
            ->all();
    }

    public function seatsAvailable(State $state, string $date, int $start, int $end): int
    {
        return (int) collect($this->survey($state, $date, $start, $end))->where('state', '!=', self::BUSY)->sum('remaining');
    }

    /**
     * @param string[] $preferred hall ids to use first
     * @param string[]|null $only restrict the allocation to these halls
     * @return array|null [{hall_id, hall_name, capacity, candidate_count, shared, invigilators[]}], null when the seats do not fit
     */
    public function allocate(State $state, string $date, int $start, int $end, int $candidates, array $preferred = [], ?array $only = null): ?array
    {
        $halls = collect($this->survey($state, $date, $start, $end))
            ->filter(fn(array $hall) => $hall['state'] !== self::BUSY && $hall['remaining'] > 0)
            ->when($only !== null, fn($halls) => $halls->filter(fn(array $hall) => in_array($hall['hall_id'], $only, true)))
            ->values();

        if ($candidates <= 0) {
            $candidates = 1;
        }

        if ($halls->sum('remaining') < $candidates) {
            return null;
        }

        $rank = fn(array $hall) => in_array($hall['hall_id'], $preferred, true) ? 0 : 1;

        $single = $halls
            ->filter(fn(array $hall) => $hall['remaining'] >= $candidates)
            ->sortBy([
                fn($a, $b) => $rank($a) <=> $rank($b),
                fn($a, $b) => ($a['state'] === self::FREE ? 0 : 1) <=> ($b['state'] === self::FREE ? 0 : 1),
                fn($a, $b) => $a['remaining'] <=> $b['remaining'],
            ])
            ->first();

        if ($single) {
            return [$this->seat($single, $candidates)];
        }

        $ordered = $halls->sortBy([
            fn($a, $b) => $rank($a) <=> $rank($b),
            fn($a, $b) => ($a['state'] === self::FREE ? 0 : 1) <=> ($b['state'] === self::FREE ? 0 : 1),
            fn($a, $b) => $b['remaining'] <=> $a['remaining'],
        ])->values();

        $left = $candidates;
        $chosen = [];

        foreach ($ordered as $hall) {
            if ($left <= 0) {
                break;
            }

            $seated = min($left, $hall['remaining']);
            $chosen[] = $this->seat($hall, $seated);
            $left -= $seated;
        }

        return $left > 0 ? null : $chosen;
    }

    private function seat(array $hall, int $candidates): array
    {
        return [
            'hall_id' => $hall['hall_id'],
            'hall_name' => $hall['hall_name'] ?? null,
            'capacity' => (int) $hall['capacity'],
            'candidate_count' => $candidates,
            'shared' => $hall['state'] === self::SHARED,
            'invigilators' => $hall['state'] === self::SHARED ? $hall['invigilators'] : [],
        ];
    }
}
