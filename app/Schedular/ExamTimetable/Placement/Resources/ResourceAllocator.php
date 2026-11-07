<?php

namespace App\Schedular\ExamTimetable\Placement\Resources;

use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorTeachesCourse;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Finds halls and invigilators for one exam session.
 *
 * Halls: enough seats for every candidate, using as few halls as possible.
 * Requested halls are tried first, then a single free hall that fits
 * (the smallest one), then the largest free halls until everyone is seated,
 * and only then halls another exam is using at exactly the same time.
 *
 * Invigilators: each hall gets its own. An invigilator is never given an exam
 * for a course they teach (to prevent exam malpractice) and never two
 * sessions at once. Requested invigilators come first, then whoever has
 * invigilated least that day and overall, so the work is shared fairly.
 */
class ResourceAllocator
{
    /**
     * @param array $request {
     *   date, start_time, end_time, course_id, slot_type,
     *   candidate_count?        defaults to the course's candidates,
     *   hall_ids?               only these halls (requested assignment),
     *   invigilator_ids?        these invigilators must be used (requested assignment),
     *   preferred_hall_ids?     tried first,
     *   preferred_invigilator_ids? tried first,
     * }
     * @return array{ok: bool, halls: array[], blockers: array[]}
     */
    public function allocate(State $state, array $request): array
    {
        $context = RequestContext::fromPayload();
        $date = $request['date'];
        $start = Time::toMinutes($request['start_time']);
        $end = Time::toMinutes($request['end_time']);
        $courseId = $request['course_id'];
        $needed = (int) ($request['candidate_count'] ?? $context->candidateCountFor($courseId));
        $conflict = $this->conflict($request);

        $halls = $this->chooseHalls($state, $context, $request, $date, $start, $end, $needed, $conflict);
        if (!$halls['ok']) {
            // Report problems with requested invigilators too, so both can be fixed at once.
            $required = $this->checkRequiredInvigilators($state, $context, $request['invigilator_ids'] ?? [], $date, $start, $end, $courseId, $conflict);
            return ['ok' => false, 'halls' => [], 'blockers' => [...$halls['blockers'], ...$required]];
        }

        $invigilators = $this->assignInvigilators($state, $context, $request, $halls['halls'], $date, $start, $end, $courseId, $conflict);
        if (!$invigilators['ok']) {
            return ['ok' => false, 'halls' => [], 'blockers' => $invigilators['blockers']];
        }

        return ['ok' => true, 'halls' => $invigilators['halls'], 'blockers' => []];
    }

    /** Records the session's halls and invigilators as busy. */
    public function commit(State $state, string $date, array $session): void
    {
        $start = Time::toMinutes($session['start_time']);
        $end = Time::toMinutes($session['end_time']);

        foreach ($session['halls'] as $hall) {
            $state->index->commitHall($hall['hall_id'], $date, $start, $end, $hall['candidate_count'], $session['source'], $session['course_id']);
            foreach ($hall['invigilators'] as $invigilatorId) {
                $state->index->commitInvigilator($invigilatorId, $date, $start, $end, $session['source'], $session['course_id']);
            }
        }
    }

    // ── Halls ────────────────────────────────────────────────────────────

    private function chooseHalls(State $state, RequestContext $context, array $request, string $date, int $start, int $end, int $needed, array $conflict): array
    {
        $fixed = $request['hall_ids'] ?? [];
        $pool = $fixed
            ? collect($fixed)->map(fn(string $id) => $context->hall($id))->filter()->values()
            : $context->halls();

        $free = [];
        $shared = [];
        $busy = [];

        foreach ($pool as $hall) {
            $seats = $state->index->hallSeatsFree($hall['hall_id'], $hall['capacity'], $date, $start, $end);
            if ($seats['seats'] <= 0) {
                $busy[] = ['hall' => $hall, 'conflict' => $seats['conflict']];
                continue;
            }
            $entry = [...$hall, 'seats' => $seats['seats'], 'shared' => $seats['shared']];
            $seats['shared'] ? $shared[] = $entry : $free[] = $entry;
        }

        if ($fixed) {
            return $this->useFixedHalls([...$free, ...$shared], $busy, $fixed, $needed, $date, $conflict, $context);
        }

        $chosen = [];
        $remaining = $needed;

        foreach ($request['preferred_hall_ids'] ?? [] as $hallId) {
            foreach ([&$free, &$shared] as &$bucket) {
                foreach ($bucket as $i => $hall) {
                    if ($remaining > 0 && $hall['hall_id'] === $hallId) {
                        $chosen[] = $this->seat($hall, $remaining);
                        unset($bucket[$i]);
                    }
                }
            }
            unset($bucket);
        }

        foreach ([$free, $shared] as $bucket) {
            while ($remaining > 0 && !empty($bucket)) {
                $pick = $this->bestFit($bucket, $remaining);
                $chosen[] = $this->seat($bucket[$pick], $remaining);
                unset($bucket[$pick]);
            }
        }

        if ($remaining > 0) {
            return ['ok' => false, 'blockers' => [[
                'key' => HallUnavailable::KEY,
                'date' => $date,
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
                'seats_needed' => $needed,
                'seats_free' => $needed - $remaining,
                'busy_hall_ids' => array_values(array_map(fn(array $entry) => $entry['hall']['hall_id'], $busy)),
                'conflict' => $conflict,
            ]]];
        }

        return ['ok' => true, 'halls' => $chosen];
    }

    private function useFixedHalls(array $available, array $busy, array $fixed, int $needed, string $date, array $conflict, RequestContext $context): array
    {
        $blockers = [];

        foreach ($busy as $entry) {
            $blockers[] = [
                'key' => HallBusy::KEY,
                'hall_id' => $entry['hall']['hall_id'],
                ...($entry['conflict'] ?? ['date' => $date]),
                'conflict' => $conflict,
            ];
        }

        $seats = array_sum(array_column($available, 'seats'));
        if (empty($busy) && $seats < $needed) {
            $blockers[] = [
                'key' => HallCapacityExceeded::KEY,
                'hall_ids' => $fixed,
                'total_capacity' => array_sum(array_map(fn(string $id) => $context->hallCapacity($id), $fixed)),
                'seats_free' => $seats,
                'total_candidates' => $needed,
                'conflict' => $conflict,
            ];
        }

        if ($blockers) {
            return ['ok' => false, 'blockers' => $blockers];
        }

        $remaining = $needed;
        $chosen = [];
        foreach ($available as $hall) {
            if ($remaining > 0) {
                $chosen[] = $this->seat($hall, $remaining);
            }
        }

        return ['ok' => true, 'halls' => $chosen];
    }

    /** The smallest hall seating everyone left, else the largest one. */
    private function bestFit(array $halls, int $remaining): int|string
    {
        $fits = array_filter($halls, fn(array $hall) => $hall['seats'] >= $remaining);
        if ($fits) {
            uasort($fits, fn(array $a, array $b) => [$a['seats'], $a['hall_name'] ?? ''] <=> [$b['seats'], $b['hall_name'] ?? '']);
            return array_key_first($fits);
        }

        uasort($halls, fn(array $a, array $b) => [$b['seats'], $a['hall_name'] ?? ''] <=> [$a['seats'], $b['hall_name'] ?? '']);
        return array_key_first($halls);
    }

    private function seat(array $hall, int &$remaining): array
    {
        $count = min($hall['seats'], $remaining);
        $remaining -= $count;

        return [
            'hall_id' => $hall['hall_id'],
            'hall_name' => $hall['hall_name'] ?? null,
            'capacity' => $hall['capacity'],
            'candidate_count' => $count,
            'shared' => $hall['shared'],
            'invigilators' => [],
        ];
    }

    // ── Invigilators ─────────────────────────────────────────────────────

    /** Requested invigilators must be free and must not teach the course. */
    private function checkRequiredInvigilators(State $state, RequestContext $context, array $required, string $date, int $start, int $end, string $courseId, array $conflict): array
    {
        $blockers = [];

        foreach ($required as $invigilatorId) {
            if ($context->teaches($invigilatorId, $courseId)) {
                $blockers[] = [
                    'key' => InvigilatorTeachesCourse::KEY,
                    'invigilator_id' => $invigilatorId,
                    'course_id' => $courseId,
                    'conflict' => $conflict,
                ];
                continue;
            }
            if ($busy = $state->index->invigilatorConflict($invigilatorId, $date, $start, $end)) {
                $blockers[] = [
                    'key' => InvigilatorBusy::KEY,
                    'invigilator_id' => $invigilatorId,
                    ...$busy,
                    'conflict' => $conflict,
                ];
            }
        }

        return $blockers;
    }

    private function assignInvigilators(State $state, RequestContext $context, array $request, array $halls, string $date, int $start, int $end, string $courseId, array $conflict): array
    {
        $required = $request['invigilator_ids'] ?? [];

        if ($blockers = $this->checkRequiredInvigilators($state, $context, $required, $date, $start, $end, $courseId, $conflict)) {
            return ['ok' => false, 'blockers' => $blockers];
        }

        $preferred = array_flip($request['preferred_invigilator_ids'] ?? []);
        $teachers = [];
        $pool = $context->invigilators()
            ->reject(fn(array $invigilator) => in_array($invigilator['invigilator_id'], $required, true))
            ->filter(function (array $invigilator) use ($state, $context, $courseId, $date, $start, $end, &$teachers) {
                $id = $invigilator['invigilator_id'];
                if ($state->index->invigilatorConflict($id, $date, $start, $end)) {
                    return false;
                }
                if ($context->teaches($id, $courseId)) {
                    $teachers[] = $id;
                    return false;
                }
                return true;
            })
            ->sortBy(fn(array $invigilator) => [
                isset($preferred[$invigilator['invigilator_id']]) ? 0 : 1,
                $state->index->invigilatorDayMinutes($invigilator['invigilator_id'], $date),
                $state->index->invigilatorTotalMinutes($invigilator['invigilator_id']),
                $invigilator['name'] ?? '',
            ])
            ->pluck('invigilator_id')
            ->values()
            ->all();

        $queue = [...$required, ...$pool];
        $neededTotal = array_sum(array_map(fn(array $hall) => $context->invigilatorsNeeded($hall['candidate_count']), $halls));
        $neededTotal = max($neededTotal, count($required));

        if (count($queue) < $neededTotal) {
            return ['ok' => false, 'blockers' => [array_filter([
                'key' => InvigilatorUnavailable::KEY,
                'date' => $date,
                'start_time' => $request['start_time'],
                'end_time' => $request['end_time'],
                'course_id' => $courseId,
                'invigilators_needed' => $neededTotal,
                'invigilators_free' => count($queue),
                // Free, but kept off this exam because they teach the course.
                'teaching_invigilator_ids' => $teachers ?: null,
                'conflict' => $conflict,
            ], fn($value) => $value !== null)]];
        }

        // Every hall first gets the invigilators it needs, then any requested
        // invigilators left over are spread over the halls.
        foreach ($halls as &$hall) {
            $count = $context->invigilatorsNeeded($hall['candidate_count']);
            for ($i = 0; $i < $count; $i++) {
                $hall['invigilators'][] = array_shift($queue);
            }
        }
        unset($hall);

        $leftOver = array_values(array_intersect($queue, $required));
        foreach ($leftOver as $i => $invigilatorId) {
            $halls[$i % count($halls)]['invigilators'][] = $invigilatorId;
        }

        return ['ok' => true, 'halls' => $halls];
    }

    private function conflict(array $request): array
    {
        return array_filter([
            'course_id' => $request['course_id'] ?? null,
            'date' => $request['date'] ?? null,
            'start_time' => $request['start_time'] ?? null,
            'end_time' => $request['end_time'] ?? null,
            'slot_type' => $request['slot_type'] ?? null,
        ]);
    }
}
