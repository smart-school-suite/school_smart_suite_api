<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use App\Schedular\ExamTimetable\Support\Time;
use Illuminate\Support\Collection;

/**
 * What the solution definitions may rely on when they look for a better date or time.
 *
 * A move is only offered where students, halls and invigilators are free of real commitments: joint course
 * sessions, the other pinned requests of this payload, and bookings of other exams. Sessions the placement
 * engine put in the grid are not commitments, because the timetable is generated again after a fix.
 */
class SolutionContext
{
    public const MAX_ALTERNATIVES = 3;

    public readonly RequestContext $context;
    private State $commitments;

    public function __construct(
        public readonly array $payload,
        public readonly ?State $state = null,
    ) {
        $this->context = RequestContext::fromPayload($payload);
        $this->commitments = new State($this->context);
    }

    public function dates(): array
    {
        return $this->context->dates();
    }

    public function window(string $date): ?array
    {
        return $this->context->window($date);
    }

    /** Sessions of the generated grid (empty when the context is built without a run). */
    public function sessions(): Collection
    {
        return collect($this->state?->sessions() ?? []);
    }

    public function courseName(?string $id): ?string
    {
        return $this->context->courseName($id);
    }

    public function hallName(?string $id): ?string
    {
        return $this->context->hallName($id);
    }

    public function invigilatorName(?string $id): ?string
    {
        return $this->context->invigilatorName($id);
    }

    public function identify(Subject $subject): ?string
    {
        return implode(', ', array_filter([
            $this->courseName($subject->get('course_id')),
            $this->invigilatorName($subject->get('invigilator_id')),
            $this->hallName($subject->get('hall_id')),
        ])) ?: null;
    }

    /**
     * True when students cannot sit an exam then: outside opening hours, during a joint course, or during
     * another pinned request. $ignore lists request entries (by subject) that are being moved.
     */
    public function isBlockedTime(string $date, int $start, int $end, ?Subject $ignore = null): bool
    {
        $window = $this->window($date);

        if (!$window || $start < $window[0] || $end > $window[1] || $end <= $start) {
            return true;
        }

        $gap = $this->context->sessionGap();

        foreach ($this->studentCommitments($date) as $commitment) {
            if ($ignore && $this->isSame($commitment, $ignore)) {
                continue;
            }

            if (Time::overlaps($start - $gap, $end + $gap, $commitment['start'], $commitment['end'])) {
                return true;
            }
        }

        return false;
    }

    public function seatsFree(string $date, int $start, int $end): int
    {
        return app(HallAvailability::class)->seatsAvailable($this->commitments, $date, $start, $end);
    }

    public function hallFits(string $hallId, string $date, int $start, int $end, int $seats, ?Subject $ignore = null): bool
    {
        $hall = $this->context->hall($hallId);

        if (!$hall) {
            return false;
        }

        $status = app(HallAvailability::class)->status($this->commitments, $hall, $date, $start, $end);

        return $status['state'] !== HallAvailability::BUSY && $status['remaining'] >= $seats
            && !$this->resourceCommitted('hall_id', $hallId, $date, $start, $end, $ignore);
    }

    public function invigilatorFree(string $invigilatorId, string $date, int $start, int $end, ?Subject $ignore = null): bool
    {
        return $this->context->invigilator($invigilatorId) !== null
            && app(InvigilatorAvailability::class)->isFree($this->commitments, $invigilatorId, $date, $start, $end)
            && !$this->resourceCommitted('invigilator_id', $invigilatorId, $date, $start, $end, $ignore);
    }

    public function anyInvigilatorFree(string $date, int $start, int $end): bool
    {
        return $this->freeInvigilators($date, $start, $end)->isNotEmpty();
    }

    public function freeInvigilators(string $date, int $start, int $end, array $exclude = []): Collection
    {
        return $this->context->invigilators()
            ->reject(fn($invigilator) => in_array($invigilator['invigilator_id'], $exclude, true))
            ->filter(fn($invigilator) => $this->invigilatorFree($invigilator['invigilator_id'], $date, $start, $end))
            ->sortBy(fn($invigilator) => $this->context->invigilatorBusyMinutes($invigilator['invigilator_id']))
            ->values();
    }

    public function freeHalls(string $date, int $start, int $end, int $seats, array $exclude = []): Collection
    {
        return $this->context->halls()
            ->reject(fn($hall) => in_array($hall['hall_id'], $exclude, true))
            ->filter(fn($hall) => $this->hallFits($hall['hall_id'], $date, $start, $end, $seats))
            ->sortBy('capacity')
            ->values();
    }

    /**
     * Start times on one date, nearest to $near first.
     *
     * @return array[] [{date, start, end, shift}]
     */
    public function findWindows(string $date, int $length, int $near, callable $accepts, array $avoid = [], ?Subject $ignore = null): array
    {
        $window = $this->window($date);

        if (!$window) {
            return [];
        }

        $step = $this->context->slotStep();
        $candidates = [];

        for ($start = (int) (ceil($window[0] / $step) * $step); $start + $length <= $window[1]; $start += $step) {
            $end = $start + $length;

            if ($this->isBlockedTime($date, $start, $end, $ignore)) {
                continue;
            }

            if (collect($avoid)->contains(fn($other) => ($other['date'] ?? $date) === $date && Time::overlaps($start, $end, $other[0], $other[1]))) {
                continue;
            }

            if (!$accepts($date, $start, $end)) {
                continue;
            }

            $candidates[] = ['date' => $date, 'start' => $start, 'end' => $end, 'shift' => abs($start - $near)];
        }

        return collect($candidates)->sortBy('shift')->values()->all();
    }

    /**
     * The closest windows on the same date, then the best window on each other date (nearest date first).
     */
    public function nearestWindows(string $date, int $start, int $length, callable $accepts, array $avoid = [], ?Subject $ignore = null, int $limit = self::MAX_ALTERNATIVES): array
    {
        $sameDate = collect($this->findWindows($date, $length, $start, $accepts, $avoid, $ignore))
            ->reject(fn($window) => $window['start'] === $start && $window['end'] === $start + $length)
            ->take($limit)
            ->map(fn($window) => [...$window, 'date_changed' => false]);

        if ($sameDate->count() >= $limit) {
            return $sameDate->values()->all();
        }

        $origin = strtotime($date) ?: 0;
        $otherDates = collect($this->dates())
            ->reject(fn($other) => $other === $date)
            ->sortBy(fn($other) => abs((strtotime($other) ?: 0) - $origin))
            ->flatMap(fn($other) => collect($this->findWindows($other, $length, $start, $accepts, $avoid, $ignore))->take(1))
            ->take($limit - $sameDate->count())
            ->map(fn($window) => [...$window, 'date_changed' => true]);

        return $sameDate->concat($otherDates)->values()->all();
    }

    /** Largest free stretch per date for the students, with the commitments above. */
    public function longestFreeStretch(string $date): int
    {
        $window = $this->window($date);

        if (!$window) {
            return 0;
        }

        $busy = collect($this->studentCommitments($date))->sortBy('start')->values();
        $cursor = $window[0];
        $longest = 0;

        foreach ($busy as $commitment) {
            $longest = max($longest, $commitment['start'] - $cursor);
            $cursor = max($cursor, $commitment['end']);
        }

        return max($longest, $window[1] - $cursor);
    }

    /** Exam sessions of the generated grid that a hall or invigilator request could be moved to. */
    public function sessionWindows(): Collection
    {
        return $this->sessions()->map(fn(ExamSessionDTO $session) => [
            'date' => $session->date,
            'start' => $session->start,
            'end' => $session->end,
            'course_id' => $session->courseId,
        ]);
    }

    private function studentCommitments(string $date): array
    {
        return collect([
            ...array_map(fn($entry) => [...$entry, 'source' => 'joint'], $this->context->jointCourses()),
            ...array_map(fn($entry) => [...$entry, 'source' => RequestedAssignment::KEY], $this->context->requestedAssignments()),
            ...array_map(fn($entry) => [...$entry, 'source' => CourseTimeRequest::KEY], $this->context->courseRequestedSlots()),
        ])->where('date', $date)->values()->all();
    }

    private function resourceCommitted(string $field, string $id, string $date, int $start, int $end, ?Subject $ignore = null): bool
    {
        return collect($this->context->requestedAssignments())
            ->reject(fn($entry) => $ignore && $this->isSame($entry, $ignore))
            ->contains(fn($entry) => $entry['date'] === $date && ($entry[$field] ?? null) === $id && Time::overlaps($start, $end, $entry['start'], $entry['end']));
    }

    private function isSame(array $entry, Subject $subject): bool
    {
        return $entry['date'] === $subject->date()
            && $entry['start'] === Time::toMinutes($subject->get('start_time'))
            && $entry['end'] === Time::toMinutes($subject->get('end_time'))
            && (!$subject->get('course_id') || ($entry['course_id'] ?? null) === $subject->get('course_id'));
    }
}
