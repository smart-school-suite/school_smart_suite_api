<?php

namespace App\Schedular\ExamTimetable\Solution\Support;

use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use App\Schedular\ExamTimetable\Solution\DTO\Subject;
use Illuminate\Support\Collection;

/**
 * What solution definitions know about a run: the scheduler input and,
 * while planning, the timetable it produced. Use these helpers rather than
 * reading the input directly.
 *
 * A move is only offered where the course's students have no other exam and
 * enough seats and invigilators (who do not teach the course) are free. The
 * course's own session in the result is ignored, since the timetable is
 * generated again once a solution is applied.
 */
final class SolutionContext
{
    public const MAX_ALTERNATIVES = 3;
    public const STEP_MINUTES = 15;

    private RequestContext $input;

    public function __construct(public readonly array $payload, public readonly ?State $state = null)
    {
        $this->input = RequestContext::for($payload);
    }

    public function input(): RequestContext
    {
        return $this->input;
    }

    // ── Names ────────────────────────────────────────────────────────────

    public function courseName(string $courseId): ?string
    {
        $course = $this->input->course($courseId);
        return $course ? ($course['course_title'] ?? $course['course_code'] ?? null) : null;
    }

    public function hallName(string $hallId): ?string
    {
        return $this->input->hall($hallId)['hall_name'] ?? null;
    }

    public function invigilatorName(string $invigilatorId): ?string
    {
        return $this->input->invigilator($invigilatorId)['name'] ?? null;
    }

    public function identify(Subject $subject): ?string
    {
        foreach (['course_id' => 'courseName', 'hall_id' => 'hallName', 'invigilator_id' => 'invigilatorName'] as $field => $lookup) {
            if ($id = $subject->get($field)) {
                return $this->{$lookup}($id) ?? null;
            }
        }
        return null;
    }

    // ── Dates and hours ──────────────────────────────────────────────────

    /** @return string[] */
    public function dates(): array
    {
        return $this->input->operationalDates();
    }

    /** @return ?array{0: int, 1: int} opening hours of a date in minutes */
    public function window(string $date): ?array
    {
        $hours = $this->input->operationalHourDate($date);
        return $hours ? [Time::toMinutes($hours['start_time']), Time::toMinutes($hours['end_time'])] : null;
    }

    // ── The generated timetable ──────────────────────────────────────────

    /** @return array[] sessions of the result, each with its date */
    public function sessions(?string $date = null): array
    {
        if (!$this->state) {
            return [];
        }

        $sessions = [];
        foreach ($this->state->dateGrid as $day => $grid) {
            if ($date !== null && $day !== $date) {
                continue;
            }
            foreach ($grid->allocations as $session) {
                $sessions[] = [...$session, 'date' => $day];
            }
        }
        return $sessions;
    }

    /** Free seats over all halls at a time; the course's own session does not count. */
    public function seatsFree(string $date, int $start, int $end, ?string $ignoreCourseId = null): int
    {
        if (!$this->state?->index) {
            return PHP_INT_MAX;
        }

        return (int) $this->input->halls()->sum(fn(array $hall) => $this->state->index->hallSeatsFree(
            $hall['hall_id'], $hall['capacity'], $date, $start, $end, $ignoreCourseId
        )['seats']);
    }

    /** Halls with free seats at a time, largest first. */
    public function freeHalls(string $date, int $start, int $end, array $except = [], ?string $ignoreCourseId = null): Collection
    {
        return $this->input->halls()
            ->reject(fn(array $hall) => in_array($hall['hall_id'], $except, true))
            ->map(fn(array $hall) => [
                ...$hall,
                'seats' => $this->state?->index
                    ? $this->state->index->hallSeatsFree($hall['hall_id'], $hall['capacity'], $date, $start, $end, $ignoreCourseId)['seats']
                    : $hall['capacity'],
            ])
            ->filter(fn(array $hall) => $hall['seats'] > 0)
            ->sortByDesc('seats')
            ->values();
    }

    /** Invigilators free at a time who do not teach the course, least busy first. */
    public function freeInvigilators(string $date, int $start, int $end, ?string $courseId, array $except = [], ?string $ignoreCourseId = null): Collection
    {
        return $this->input->invigilators()
            ->reject(fn(array $invigilator) => in_array($invigilator['invigilator_id'], $except, true))
            ->reject(fn(array $invigilator) => $courseId && $this->input->teaches($invigilator['invigilator_id'], $courseId))
            ->reject(fn(array $invigilator) => $this->state?->index?->invigilatorConflict($invigilator['invigilator_id'], $date, $start, $end, $ignoreCourseId) !== null)
            ->sortBy(fn(array $invigilator) => [
                $this->state?->index?->invigilatorTotalMinutes($invigilator['invigilator_id']) ?? 0,
                $invigilator['name'] ?? '',
            ])
            ->values();
    }

    /** True when the course's exam could take place then. */
    public function canHost(string $courseId, string $date, int $start, int $end): bool
    {
        $window = $this->window($date);
        if (!$window || $start < $window[0] || $end > $window[1]) {
            return false;
        }

        foreach ($this->sessions($date) as $session) {
            if ($session['course_id'] !== $courseId
                && Time::overlaps($start, $end, Time::toMinutes($session['start_time']), Time::toMinutes($session['end_time']))) {
                return false;
            }
        }

        $needed = $this->input->candidateCountFor($courseId);
        $halls = $this->freeHalls($date, $start, $end, [], $courseId);
        $hallsUsed = 0;
        $seated = 0;
        foreach ($halls as $hall) {
            if ($seated >= $needed) {
                break;
            }
            $seated += $hall['seats'];
            $hallsUsed++;
        }

        if ($seated < $needed) {
            return false;
        }

        return $this->freeInvigilators($date, $start, $end, $courseId, [], $courseId)->count() >= max(1, $hallsUsed);
    }

    /**
     * Nearest times to a window that $accepts, best first: the same date
     * shifted by as little as possible, then other dates at the same time,
     * then other dates at any time.
     *
     * @param callable(string $date, int $start, int $end): bool $accepts
     * @param array[] $avoid [{date, start, end}] times that must stay free
     * @return array[] [{date, start, end, shift, date_changed}]
     */
    public function nearestWindows(string $date, int $start, int $end, callable $accepts, array $avoid = []): array
    {
        $duration = $end - $start;
        $found = [];

        $try = function (string $candidateDate, int $candidateStart) use ($accepts, $avoid, $duration, $date, $start, &$found): void {
            $candidateEnd = $candidateStart + $duration;

            foreach ($avoid as $window) {
                if ($window['date'] === $candidateDate && Time::overlaps($candidateStart, $candidateEnd, $window['start'], $window['end'])) {
                    return;
                }
            }

            if ($accepts($candidateDate, $candidateStart, $candidateEnd)) {
                $found[] = [
                    'date' => $candidateDate,
                    'start' => $candidateStart,
                    'end' => $candidateEnd,
                    'shift' => abs($candidateStart - $start),
                    'date_changed' => $candidateDate !== $date,
                ];
            }
        };

        $sameDate = $this->window($date) ? $this->startTimes($date, $duration, $start, exclude: $start) : [];
        foreach ($sameDate as $candidate) {
            $try($date, $candidate);
            if (count($found) >= self::MAX_ALTERNATIVES) {
                return $found;
            }
        }

        $otherDates = array_values(array_filter($this->dates(), fn(string $other) => $other !== $date));
        usort($otherDates, fn(string $a, string $b) => abs(strtotime($a) - strtotime($date)) <=> abs(strtotime($b) - strtotime($date)));

        foreach ($otherDates as $other) {
            foreach ($this->startTimes($other, $duration, $start) as $candidate) {
                $try($other, $candidate);
                if (count($found) >= self::MAX_ALTERNATIVES) {
                    return $found;
                }
                // One option per other date is enough.
                if (end($found) && end($found)['date'] === $other) {
                    break;
                }
            }
        }

        return $found;
    }

    /** Start times on a date for a session of $duration, nearest to $near first. */
    private function startTimes(string $date, int $duration, int $near, ?int $exclude = null): array
    {
        $window = $this->window($date);
        if (!$window) {
            return [];
        }

        $first = (int) ceil($window[0] / self::STEP_MINUTES) * self::STEP_MINUTES;
        $times = [];
        for ($time = $first; $time + $duration <= $window[1]; $time += self::STEP_MINUTES) {
            if ($time !== $exclude) {
                $times[] = $time;
            }
        }

        usort($times, fn(int $a, int $b) => [abs($a - $near), $a] <=> [abs($b - $near), $b]);

        return $times;
    }
}
