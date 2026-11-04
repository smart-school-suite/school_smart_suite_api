<?php

namespace App\Schedular\ExamTimetable\Helpers;

use App\Schedular\ExamTimetable\Core\State;

/**
 * Finds invigilators for an exam session.
 *
 * An invigilator is free when no other exam (already active for another class, or placed in this run)
 * overlaps the window. People who teach the course are used only when nobody else is free, and the
 * least loaded people are picked first so the work is spread out.
 */
class InvigilatorAvailability
{
    public function isFree(State $state, string $invigilatorId, string $date, int $start, int $end): bool
    {
        foreach ($state->context->invigilatorBusy($invigilatorId, $date) as $slot) {
            if ($slot['start'] < $end && $slot['end'] > $start) {
                return false;
            }
        }

        foreach ($state->sessionsOn($date) as $session) {
            if ($session->overlaps($start, $end) && in_array($invigilatorId, $session->invigilatorIds(), true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $preferred invigilator ids to use first
     * @param string[] $exclude
     * @return array[] [{invigilator_id, name, teaches_course, load}]
     */
    public function available(State $state, ?string $courseId, string $date, int $start, int $end, array $preferred = [], array $exclude = []): array
    {
        $assigned = $this->assignedMinutes($state);

        return $state->context->invigilators()
            ->reject(fn(array $invigilator) => in_array($invigilator['invigilator_id'], $exclude, true))
            ->filter(fn(array $invigilator) => $this->isFree($state, $invigilator['invigilator_id'], $date, $start, $end))
            ->map(fn(array $invigilator) => [
                'invigilator_id' => $invigilator['invigilator_id'],
                'name' => $invigilator['name'] ?? null,
                'teaches_course' => $courseId ? $state->context->teaches($invigilator['invigilator_id'], $courseId) : false,
                'load' => $state->context->invigilatorBusyMinutes($invigilator['invigilator_id']) + ($assigned[$invigilator['invigilator_id']] ?? 0),
                'preferred' => in_array($invigilator['invigilator_id'], $preferred, true),
            ])
            ->sortBy([
                fn($a, $b) => $b['preferred'] <=> $a['preferred'],
                fn($a, $b) => $a['teaches_course'] <=> $b['teaches_course'],
                fn($a, $b) => $a['load'] <=> $b['load'],
                fn($a, $b) => strcmp((string) $a['invigilator_id'], (string) $b['invigilator_id']),
            ])
            ->values()
            ->all();
    }

    /**
     * Gives every hall that has no invigilator yet one free invigilator.
     *
     * @param string[] $fixed invigilators that must be used (put in the first hall that needs one)
     * @return array|null the halls with invigilators, or null when there are not enough free people
     */
    public function staff(State $state, array $halls, ?string $courseId, string $date, int $start, int $end, array $preferred = [], array $fixed = []): ?array
    {
        $used = collect($halls)->flatMap(fn($hall) => $hall['invigilators'])->all();
        $pool = collect($fixed)
            ->reject(fn($id) => in_array($id, $used, true))
            ->map(fn($id) => ['invigilator_id' => $id])
            ->concat($this->available($state, $courseId, $date, $start, $end, $preferred, [...$used, ...$fixed]))
            ->values();

        foreach ($halls as $index => $hall) {
            if (!empty($hall['invigilators'])) {
                continue;
            }

            $next = $pool->shift();

            if (!$next) {
                return null;
            }

            $halls[$index]['invigilators'] = [$next['invigilator_id']];
        }

        // Fixed invigilators that were not needed for an empty hall still join the session.
        foreach ($pool as $remaining) {
            if (in_array($remaining['invigilator_id'], $fixed, true)) {
                $halls[0]['invigilators'][] = $remaining['invigilator_id'];
            }
        }

        return $halls;
    }

    public function needed(array $halls): int
    {
        return collect($halls)->filter(fn($hall) => empty($hall['invigilators']))->count();
    }

    private function assignedMinutes(State $state): array
    {
        $minutes = [];

        foreach ($state->sessions() as $session) {
            foreach ($session->invigilatorIds() as $id) {
                $minutes[$id] = ($minutes[$id] ?? 0) + ($session->end - $session->start);
            }
        }

        return $minutes;
    }
}
