<?php

namespace App\Schedular\SemesterTimetable\Solution\Support;

use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Solution\DTO\Subject;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use Illuminate\Support\Collection;

class SolutionContext
{
    public const MAX_ALTERNATIVES = 3;

    public readonly ConstraintContext $constraints;
    private array $periodCache = [];

    public function __construct(
        public readonly array $payload,
        public readonly array $grid,
        public readonly bool $withPreference,
    ) {
        $this->constraints = ConstraintContext::fromPayload($payload);
    }

    public function withPayload(array $payload): self
    {
        return $payload === $this->payload ? $this : new self($payload, $this->grid, $this->withPreference);
    }

    public function days(): array
    {
        return $this->constraints->opDays()->map(fn($day) => strtolower($day))->values()->all();
    }

    public function window(string $day): array
    {
        $window = $this->constraints->resolveOperationalWindow(strtolower($day));

        return [Time::toMinutes($window['start']) ?? 480, Time::toMinutes($window['end']) ?? 1020];
    }

    public function periodDuration(string $day): int
    {
        return max(1, $this->constraints->resolvePeriodDuration(strtolower($day)));
    }

    public function breakWindow(string $day): ?array
    {
        $window = $this->constraints->resolveBreakWindow(strtolower($day));

        return $window ? [Time::toMinutes($window['start']), Time::toMinutes($window['end'])] : null;
    }

    public function jointCourses(string $day): Collection
    {
        return $this->constraints->jointCourses($day)->map(fn($course) => [
            ...$course,
            'start' => Time::toMinutes($course['start_time'] ?? null),
            'end' => Time::toMinutes($course['end_time'] ?? null),
        ])->filter(fn($course) => $course['start'] !== null && $course['end'] !== null)->values();
    }

    public function requestedFreePeriods(string $day): Collection
    {
        return $this->constraints->requestedFreePeriodsFor($day)->map(fn($period) => [
            'start' => Time::toMinutes($period['start_time'] ?? null),
            'end' => Time::toMinutes($period['end_time'] ?? null),
        ])->filter(fn($period) => $period['start'] !== null && $period['end'] !== null)->values();
    }

    public function periods(string $day): array
    {
        $day = strtolower($day);

        if (isset($this->periodCache[$day])) {
            return $this->periodCache[$day];
        }

        $fromGrid = collect($this->grid)
            ->filter(fn($slot) => $slot instanceof GridSlotDTO && strtolower($slot->day) === $day && $slot->type !== GridSlotDTO::TYPE_BREAK)
            ->map(fn(GridSlotDTO $slot) => [Time::toMinutes($slot->start_time), Time::toMinutes($slot->end_time)])
            ->filter(fn($period) => $period[0] !== null && $period[1] !== null)
            ->sortBy(fn($period) => $period[0])
            ->values()
            ->all();

        return $this->periodCache[$day] = $fromGrid ?: $this->generatePeriods($day);
    }

    private function generatePeriods(string $day): array
    {
        [$start, $end] = $this->window($day);
        $duration = $this->periodDuration($day);
        $break = $this->breakWindow($day);
        $periods = [];
        $cursor = $start;

        while ($cursor + $duration <= $end) {
            if ($break && Time::overlaps($cursor, $cursor + $duration, $break[0], $break[1])) {
                $cursor = max($cursor + 1, $break[1]);
                continue;
            }

            $periods[] = [$cursor, $cursor + $duration];
            $cursor += $duration;
        }

        return $periods;
    }

    public function isBlockedTime(string $day, int $start, int $end, array $ignore = []): bool
    {
        [$windowStart, $windowEnd] = $this->window($day);

        if ($start < $windowStart || $end > $windowEnd || $end <= $start) {
            return true;
        }

        $break = $this->breakWindow($day);
        if (!in_array('break', $ignore, true) && $break && Time::overlaps($start, $end, $break[0], $break[1])) {
            return true;
        }

        if (!in_array('joint', $ignore, true) && $this->jointCourses($day)->contains(fn($course) => Time::overlaps($start, $end, $course['start'], $course['end']))) {
            return true;
        }

        return !in_array('free', $ignore, true)
            && $this->requestedFreePeriods($day)->contains(fn($period) => Time::overlaps($start, $end, $period['start'], $period['end']));
    }

    public function teacherFree(string $teacherId, string $day, int $start, int $end): bool
    {
        $busy = $this->constraints->tBusySlotsFor($teacherId, $day)
            ->contains(fn($slot) => Time::overlaps($start, $end, Time::toMinutes($slot['start_time']), Time::toMinutes($slot['end_time'])));

        if ($busy || $this->committed($day, $start, $end, teacherId: $teacherId)) {
            return false;
        }

        if (!$this->withPreference) {
            return true;
        }

        $preferences = $this->constraints->tPreferredSlotsFor($teacherId, $day);

        return $preferences->contains(
            fn($slot) => $start >= Time::toMinutes($slot['start_time']) && $end <= Time::toMinutes($slot['end_time'])
        );
    }

    public function hallFree(string $hallId, string $day, int $start, int $end): bool
    {
        $busy = $this->constraints->hBusySlotsForHallIdDay($hallId, $day)
            ->contains(fn($slot) => Time::overlaps($start, $end, Time::toMinutes($slot['start_time']), Time::toMinutes($slot['end_time'])));

        return !$busy && !$this->committed($day, $start, $end, hallId: $hallId);
    }

    public function anyTeacherFree(string $day, int $start, int $end, ?string $courseId = null): bool
    {
        return $this->freeTeachers($day, $start, $end, $courseId)->isNotEmpty();
    }

    public function anyHallFree(string $day, int $start, int $end): bool
    {
        return $this->freeHalls($day, $start, $end)->isNotEmpty();
    }

    public function freeTeachers(string $day, int $start, int $end, ?string $courseId = null, array $exclude = []): Collection
    {
        $teachers = $courseId
            ? $this->constraints->teachersForCourse($courseId)
            : $this->constraints->teachers();

        return collect($teachers)
            ->map(fn($teacher) => is_array($teacher) ? $teacher : ($this->constraints->teacher($teacher) ?? ['teacher_id' => $teacher]))
            ->filter(fn($teacher) => !empty($teacher['teacher_id']) && !in_array($teacher['teacher_id'], $exclude, true))
            ->unique('teacher_id')
            ->filter(fn($teacher) => $this->teacherFree($teacher['teacher_id'], $day, $start, $end))
            ->map(fn($teacher) => [...$teacher, 'load_minutes' => $this->teacherLoad($teacher['teacher_id'])])
            ->sortBy('load_minutes')
            ->values();
    }

    public function freeHalls(string $day, int $start, int $end, array $exclude = []): Collection
    {
        return $this->constraints->halls()
            ->filter(fn($hall) => !empty($hall['hall_id']) && !in_array($hall['hall_id'], $exclude, true))
            ->filter(fn($hall) => $this->hallFree($hall['hall_id'], $day, $start, $end))
            ->sortByDesc(fn($hall) => $hall['hall_capacity'] ?? 0)
            ->values();
    }

    public function teacherName(string $teacherId): ?string
    {
        return $this->constraints->teacher($teacherId)['name'] ?? null;
    }

    public function hallName(string $hallId): ?string
    {
        return $this->constraints->hall($hallId)['hall_name'] ?? null;
    }

    public function courseName(string $courseId): ?string
    {
        return $this->constraints->tCourses()->firstWhere('course_id', $courseId)['course_title'] ?? null;
    }

    public function identify(Subject $subject): ?string
    {
        $courseId = $subject->get('course_id');
        $teacherId = $subject->get('teacher_id');
        $hallId = $subject->get('hall_id');

        $course = $courseId
            ? trim($courseId . ' ' . ($this->constraints->tCourses()->firstWhere('course_id', $courseId)['course_title'] ?? ''))
            : null;
        $teacher = $teacherId ? ($this->constraints->teacher($teacherId)['name'] ?? $teacherId) : null;
        $hall = $hallId ? ($this->constraints->hall($hallId)['hall_name'] ?? $hallId) : null;

        return implode(', ', array_filter([$course, $teacher, $hall])) ?: null;
    }

    public function teacherLoad(string $teacherId): int
    {
        $busy = $this->constraints->tBusySlots()
            ->filter(fn($slot) => ($slot['teacher_id'] ?? null) === $teacherId)
            ->sum(fn($slot) => Time::toMinutes($slot['end_time']) - Time::toMinutes($slot['start_time']));

        $scheduled = collect($this->grid)
            ->filter(fn($slot) => $slot instanceof GridSlotDTO && $slot->teacher_id === $teacherId)
            ->sum(fn(GridSlotDTO $slot) => Time::toMinutes($slot->end_time) - Time::toMinutes($slot->start_time));

        return (int) ($busy + $scheduled);
    }

    public function findWindows(string $day, int $length, int $near, callable $accepts, array $avoid = [], array $ignore = []): array
    {
        $day = strtolower($day);
        $candidates = [];

        foreach ($this->periods($day) as [$periodStart]) {
            $start = $periodStart;
            $end = $start + $length;

            if ($this->isBlockedTime($day, $start, $end, $ignore)) {
                continue;
            }

            if (collect($avoid)->contains(fn($window) => ($window['day'] ?? $day) === $day && Time::overlaps($start, $end, $window[0], $window[1]))) {
                continue;
            }

            if (!$accepts($day, $start, $end)) {
                continue;
            }

            $candidates[$start] = ['day' => $day, 'start' => $start, 'end' => $end, 'shift' => abs($start - $near)];
        }

        return collect($candidates)->sortBy('shift')->values()->all();
    }

    public function nearestWindows(string $day, int $start, int $end, callable $accepts, array $avoid = [], array $ignore = [], int $limit = self::MAX_ALTERNATIVES): array
    {
        $length = $end - $start;
        $sameDay = collect($this->findWindows($day, $length, $start, $accepts, $avoid, $ignore))
            ->reject(fn($window) => $window['start'] === $start)
            ->take($limit)
            ->map(fn($window) => [...$window, 'day_changed' => false]);

        if ($sameDay->count() >= $limit) {
            return $sameDay->values()->all();
        }

        $otherDays = collect($this->days())
            ->reject(fn($other) => $other === strtolower($day))
            ->flatMap(fn($other) => collect($this->findWindows($other, $length, $start, $accepts, $avoid, $ignore))->take(1))
            ->sortBy('shift')
            ->take($limit - $sameDay->count())
            ->map(fn($window) => [...$window, 'day_changed' => true]);

        return $sameDay->concat($otherDays)->values()->all();
    }

    private function committed(string $day, int $start, int $end, ?string $teacherId = null, ?string $hallId = null): bool
    {
        $requests = collect([
            ...$this->constraints->requestedAssignmentsFor($day),
            ...$this->constraints->tRequestedWindowsFor($day),
            ...$this->constraints->hRequestedWindowsFor($day),
        ]);

        $jointCourses = $this->jointCourses($day)->map(fn($course) => [
            ...$course,
            'start_time' => Time::toTime($course['start']),
            'end_time' => Time::toTime($course['end']),
        ]);

        return $requests->concat($jointCourses)->contains(function ($commitment) use ($start, $end, $teacherId, $hallId) {
            $matches = ($teacherId && ($commitment['teacher_id'] ?? null) === $teacherId)
                || ($hallId && ($commitment['hall_id'] ?? null) === $hallId);

            return $matches && Time::overlaps($start, $end, Time::toMinutes($commitment['start_time'] ?? null) ?? -1, Time::toMinutes($commitment['end_time'] ?? null) ?? -1);
        });
    }
}
