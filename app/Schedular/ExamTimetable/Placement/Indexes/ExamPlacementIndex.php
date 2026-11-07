<?php

namespace App\Schedular\ExamTimetable\Placement\Indexes;

use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Who and what is busy when, for the run in progress.
 *
 * It starts from the busy periods in the input (other exams' active
 * timetables) and every session this run places is committed to it, so later
 * placements see the halls and invigilators it took.
 */
class ExamPlacementIndex
{
    /** @var array<string, array<string, array[]>> hall_id => date => [{start, end, seats, shareable, source, course_id}] */
    private array $hallUse = [];

    /** @var array<string, array<string, array[]>> invigilator_id => date => [{start, end, source, course_id}] */
    private array $invigilatorUse = [];

    /** @var array<string, int> minutes each invigilator invigilates in this run */
    private array $invigilatorMinutes = [];

    public static function fromContext(RequestContext $context): self
    {
        $index = new self();

        foreach ($context->hallBusyPeriods() as $period) {
            $start = Time::normalize($period['start_time'] ?? null);
            $end = Time::normalize($period['end_time'] ?? null);
            if (!$start || !$end || empty($period['hall_id']) || empty($period['date'])) {
                continue;
            }
            $capacity = $context->hallCapacity($period['hall_id']);
            $index->hallUse[$period['hall_id']][$period['date']][] = [
                'start' => Time::toMinutes($start),
                'end' => Time::toMinutes($end),
                // Another exam seated there: its free seats can be shared, but only
                // by a session with exactly the same times.
                'seats' => isset($period['candidate_count']) ? (int) $period['candidate_count'] : $capacity,
                'shareable' => isset($period['candidate_count']),
                'source' => $period['source'] ?? 'other_exam',
                'course_id' => $period['course_id'] ?? null,
            ];
        }

        foreach ($context->invigilatorBusyPeriods() as $period) {
            $start = Time::normalize($period['start_time'] ?? null);
            $end = Time::normalize($period['end_time'] ?? null);
            if (!$start || !$end || empty($period['invigilator_id']) || empty($period['date'])) {
                continue;
            }
            $index->invigilatorUse[$period['invigilator_id']][$period['date']][] = [
                'start' => Time::toMinutes($start),
                'end' => Time::toMinutes($end),
                'source' => $period['source'] ?? 'other_exam',
                'course_id' => $period['course_id'] ?? null,
            ];
        }

        return $index;
    }

    /**
     * Seats of a hall still free for a session from $start to $end.
     *
     * @return array{seats: int, shared: bool, conflict: ?array}
     */
    public function hallSeatsFree(string $hallId, int $capacity, string $date, int $start, int $end, ?string $ignoreCourseId = null): array
    {
        $used = 0;
        $shared = false;

        foreach ($this->hallUse[$hallId][$date] ?? [] as $period) {
            if ($this->ignored($period, $ignoreCourseId) || !Time::overlaps($start, $end, $period['start'], $period['end'])) {
                continue;
            }

            $sameTimes = $period['start'] === $start && $period['end'] === $end;
            if (!$period['shareable'] || !$sameTimes) {
                return ['seats' => 0, 'shared' => false, 'conflict' => $this->describe($period, $date)];
            }

            $used += $period['seats'];
            $shared = true;
        }

        $free = max(0, $capacity - $used);

        return [
            'seats' => $free,
            'shared' => $shared,
            'conflict' => $free === 0 && $shared ? ['date' => $date, 'start_time' => Time::toTime($start), 'end_time' => Time::toTime($end), 'source' => 'hall_full'] : null,
        ];
    }

    /** @return ?array the period keeping the invigilator busy, or null when free */
    public function invigilatorConflict(string $invigilatorId, string $date, int $start, int $end, ?string $ignoreCourseId = null): ?array
    {
        foreach ($this->invigilatorUse[$invigilatorId][$date] ?? [] as $period) {
            if (!$this->ignored($period, $ignoreCourseId) && Time::overlaps($start, $end, $period['start'], $period['end'])) {
                return $this->describe($period, $date);
            }
        }

        return null;
    }

    public function invigilatorDayMinutes(string $invigilatorId, string $date): int
    {
        return array_sum(array_map(
            fn(array $period) => $period['end'] - $period['start'],
            $this->invigilatorUse[$invigilatorId][$date] ?? []
        ));
    }

    public function invigilatorTotalMinutes(string $invigilatorId): int
    {
        return $this->invigilatorMinutes[$invigilatorId] ?? 0;
    }

    public function commitHall(string $hallId, string $date, int $start, int $end, int $seats, string $source, ?string $courseId): void
    {
        $this->hallUse[$hallId][$date][] = [
            'start' => $start,
            'end' => $end,
            'seats' => $seats,
            // Sessions of this run seat the same students, so they never share.
            'shareable' => false,
            'source' => $source,
            'course_id' => $courseId,
        ];
    }

    public function commitInvigilator(string $invigilatorId, string $date, int $start, int $end, string $source, ?string $courseId): void
    {
        $this->invigilatorUse[$invigilatorId][$date][] = [
            'start' => $start,
            'end' => $end,
            'source' => $source,
            'course_id' => $courseId,
        ];
        $this->invigilatorMinutes[$invigilatorId] = ($this->invigilatorMinutes[$invigilatorId] ?? 0) + ($end - $start);
    }

    /** Periods of this run's own session for a course, skipped when looking for a new time for it. */
    private function ignored(array $period, ?string $ignoreCourseId): bool
    {
        return $ignoreCourseId !== null && $period['course_id'] === $ignoreCourseId && $period['source'] !== 'other_exam';
    }

    private function describe(array $period, string $date): array
    {
        return array_filter([
            'date' => $date,
            'start_time' => Time::toTime($period['start']),
            'end_time' => Time::toTime($period['end']),
            'source' => $period['source'],
            'course_id' => $period['course_id'],
        ]);
    }
}
