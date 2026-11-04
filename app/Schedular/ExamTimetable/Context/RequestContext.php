<?php

namespace App\Schedular\ExamTimetable\Context;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Schedular\ExamTimetable\Support\Time;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Read-only view of one scheduler payload.
 *
 * Payload shape (built by GenerateFixedExamTimetable):
 *   start_date, end_date, specialty_id, candidate_count,
 *   courses[]                {course_id, course_name, candidate_count?}
 *   halls[]                  {hall_id, hall_name, capacity}
 *   invigilators[]           {invigilator_id, name, course_taught[]}
 *   hall_busy_slots[]        {hall_id, date, slots[{start_time, end_time, invigilators[], candidate_groups[]}]}
 *   invigilator_busy_slots[] {invigilator_id, date, slots[{start_time, end_time}]}
 *   hard_constraints         operational_period, session_duration, required_joint_course_periods
 *   soft_constraints         requested_assignment, course_requested_slot, invigilator_requested_slot,
 *                            hall_requested_slot, student_daily_load_range
 *
 * Constraint values use the same keys and shapes as the generate request, so a solution that edits
 * the request can be replayed on the payload.
 */
class RequestContext
{
    public const DEFAULT_START = '08:00';
    public const DEFAULT_END = '17:00';
    public const DEFAULT_DURATION = 120;

    private array $hallBusy;
    private array $invigilatorBusy;
    private ?array $dates = null;

    private function __construct(private readonly array $payload)
    {
        $this->hallBusy = $this->indexBusy($payload['hall_busy_slots'] ?? [], 'hall_id');
        $this->invigilatorBusy = $this->indexBusy($payload['invigilator_busy_slots'] ?? [], 'invigilator_id');
    }

    public static function fromPayload(array $payload): self
    {
        return new self($payload);
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function hard(string $key, mixed $default = null): mixed
    {
        return $this->payload['hard_constraints'][$key] ?? $default;
    }

    public function soft(string $key, mixed $default = null): mixed
    {
        return $this->payload['soft_constraints'][$key] ?? $default;
    }

    public function specialtyId(): ?string
    {
        return $this->payload['specialty_id'] ?? null;
    }

    public function startDate(): ?string
    {
        return Time::date($this->payload['start_date'] ?? null);
    }

    public function endDate(): ?string
    {
        return Time::date($this->payload['end_date'] ?? null) ?? $this->startDate();
    }

    // ---------------------------------------------------------------------
    // Dates and operational hours
    // ---------------------------------------------------------------------

    /** Every operational date of the exam window, in order. */
    public function dates(): array
    {
        if ($this->dates !== null) {
            return $this->dates;
        }

        $start = $this->startDate();
        $end = $this->endDate();

        if (!$start || !$end || $end < $start) {
            return $this->dates = [];
        }

        $op = $this->hard(OperationalPeriod::KEY, []);
        $excludedDates = collect($op['excluded_dates'] ?? [])->map(fn($date) => Time::date($date))->filter()->all();
        $excludedDays = collect($op['excluded_days'] ?? [])->map(fn($day) => strtolower($day))->all();
        $dates = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $value = $date->format('Y-m-d');

            if (in_array($value, $excludedDates, true) || in_array(strtolower($date->format('l')), $excludedDays, true)) {
                continue;
            }

            $dates[] = $value;
        }

        return $this->dates = $dates;
    }

    public function isInExamWindow(string $date): bool
    {
        return $this->startDate() && $date >= $this->startDate() && $date <= $this->endDate();
    }

    public function isOperationalDate(string $date): bool
    {
        return in_array($date, $this->dates(), true);
    }

    /** [start, end] in minutes for the date, or null when no exams can run that date. */
    public function window(string $date): ?array
    {
        if (!$this->isOperationalDate($date)) {
            return null;
        }

        return $this->hoursFor($date);
    }

    /** Opening hours for any date, whether or not it is operational. */
    public function hoursFor(string $date): array
    {
        $op = $this->hard(OperationalPeriod::KEY, []);
        $exception = collect($op['date_exceptions'] ?? [])->first(fn($entry) => Time::date($entry['date'] ?? null) === $date);
        $source = $exception ?? $op;

        return [
            Time::toMinutes($source['start_time'] ?? null) ?? Time::toMinutes($op['start_time'] ?? null) ?? Time::toMinutes(self::DEFAULT_START),
            Time::toMinutes($source['end_time'] ?? null) ?? Time::toMinutes($op['end_time'] ?? null) ?? Time::toMinutes(self::DEFAULT_END),
        ];
    }

    public function slotStep(): int
    {
        return max(5, (int) ($this->hard(OperationalPeriod::KEY, [])['slot_step_minutes'] ?? 15));
    }

    /** Minimum minutes between two exams of the same students. */
    public function sessionGap(): int
    {
        return max(0, (int) ($this->hard(SessionDuration::KEY, [])['gap_minutes'] ?? 0));
    }

    // ---------------------------------------------------------------------
    // Courses
    // ---------------------------------------------------------------------

    public function courses(): Collection
    {
        return collect($this->payload['courses'] ?? [])
            ->filter(fn($course) => !empty($course['course_id']))
            ->unique('course_id')
            ->values();
    }

    public function course(string $courseId): ?array
    {
        return $this->courses()->firstWhere('course_id', $courseId);
    }

    public function courseName(?string $courseId): ?string
    {
        if (!$courseId) {
            return null;
        }

        return $this->course($courseId)['course_name']
            ?? collect($this->jointCourses())->firstWhere('course_id', $courseId)['course_name']
            ?? null;
    }

    public function candidateCount(?string $courseId = null): int
    {
        $course = $courseId ? $this->course($courseId) : null;

        return max(0, (int) ($course['candidate_count'] ?? $this->payload['candidate_count'] ?? 0));
    }

    public function duration(string $courseId): int
    {
        $config = $this->hard(SessionDuration::KEY, []);
        $exception = collect($config['course_exceptions'] ?? [])->firstWhere('course_id', $courseId);

        return max(1, (int) ($exception['duration'] ?? $config['duration'] ?? self::DEFAULT_DURATION));
    }

    public function hasDurationException(string $courseId): bool
    {
        return collect($this->hard(SessionDuration::KEY, [])['course_exceptions'] ?? [])->contains('course_id', $courseId);
    }

    // ---------------------------------------------------------------------
    // Halls
    // ---------------------------------------------------------------------

    public function halls(): Collection
    {
        return collect($this->payload['halls'] ?? [])
            ->filter(fn($hall) => !empty($hall['hall_id']))
            ->map(fn($hall) => [...$hall, 'capacity' => (int) ($hall['capacity'] ?? 0)])
            ->unique('hall_id')
            ->values();
    }

    public function hall(string $hallId): ?array
    {
        return $this->halls()->firstWhere('hall_id', $hallId);
    }

    public function hallName(?string $hallId): ?string
    {
        return $hallId ? ($this->hall($hallId)['hall_name'] ?? null) : null;
    }

    /** @return array[] [{start, end, candidate_count, invigilators[]}] */
    public function hallBusy(string $hallId, string $date): array
    {
        return $this->hallBusy[$hallId][$date] ?? [];
    }

    // ---------------------------------------------------------------------
    // Invigilators
    // ---------------------------------------------------------------------

    public function invigilators(): Collection
    {
        return collect($this->payload['invigilators'] ?? [])
            ->filter(fn($invigilator) => !empty($invigilator['invigilator_id']))
            ->unique('invigilator_id')
            ->values();
    }

    public function invigilator(string $invigilatorId): ?array
    {
        return $this->invigilators()->firstWhere('invigilator_id', $invigilatorId);
    }

    public function invigilatorName(?string $invigilatorId): ?string
    {
        return $invigilatorId ? ($this->invigilator($invigilatorId)['name'] ?? null) : null;
    }

    public function teaches(string $invigilatorId, string $courseId): bool
    {
        return in_array($courseId, $this->invigilator($invigilatorId)['course_taught'] ?? [], true);
    }

    /** @return array[] [{start, end}] */
    public function invigilatorBusy(string $invigilatorId, string $date): array
    {
        return $this->invigilatorBusy[$invigilatorId][$date] ?? [];
    }

    public function invigilatorBusyMinutes(string $invigilatorId): int
    {
        return collect($this->invigilatorBusy[$invigilatorId] ?? [])
            ->flatten(1)
            ->sum(fn($slot) => $slot['end'] - $slot['start']);
    }

    // ---------------------------------------------------------------------
    // Constraints
    // ---------------------------------------------------------------------

    public function jointCourses(): array
    {
        return $this->entries($this->hard(RequiredJointCourseSession::KEY, []));
    }

    public function requestedAssignments(): array
    {
        return $this->entries($this->soft(RequestedAssignment::KEY, []));
    }

    public function courseRequestedSlots(): array
    {
        return $this->entries($this->soft(CourseTimeRequest::KEY, []));
    }

    public function invigilatorRequestedSlots(): array
    {
        return $this->entries($this->soft(InvigilatorRequestedSlot::KEY, []));
    }

    public function hallRequestedSlots(): array
    {
        return $this->entries($this->soft(HallRequestedSlot::KEY, []));
    }

    /** [min, max] sessions per day for the date; null values mean no limit. */
    public function dailyLoad(string $date): array
    {
        $range = $this->soft(StudentDailyLoadRange::KEY);

        if (!is_array($range)) {
            return [null, null];
        }

        $exception = collect($range['date_exceptions'] ?? [])->first(fn($entry) => Time::date($entry['date'] ?? null) === $date);
        $source = $exception ?? $range;

        $max = $source['max_sessions'] ?? $range['max_sessions'] ?? null;
        $min = $source['min_sessions'] ?? $range['min_sessions'] ?? null;

        return [is_numeric($min) ? (int) $min : null, is_numeric($max) ? (int) $max : null];
    }

    /** Entries with a normalised date and minutes, entries without a usable time are dropped. */
    private function entries(mixed $entries): array
    {
        return collect(is_array($entries) ? $entries : [])
            ->filter(fn($entry) => is_array($entry))
            ->map(function (array $entry) {
                $entry['date'] = Time::date($entry['date'] ?? null);
                $entry['start'] = Time::toMinutes($entry['start_time'] ?? null);
                $entry['end'] = Time::toMinutes($entry['end_time'] ?? null);

                return $entry;
            })
            ->filter(fn(array $entry) => $entry['date'] && $entry['start'] !== null && $entry['end'] !== null && $entry['end'] > $entry['start'])
            ->values()
            ->all();
    }

    /**
     * Accepts both the grouped shape {id, date, slots[]} and flat entries {id, date, start_time, end_time}.
     */
    private function indexBusy(array $entries, string $idField): array
    {
        $index = [];

        foreach ($entries as $entry) {
            $id = $entry[$idField] ?? null;
            $date = Time::date($entry['date'] ?? null);

            if (!$id || !$date) {
                continue;
            }

            $slots = isset($entry['slots']) && is_array($entry['slots']) ? $entry['slots'] : [$entry];

            foreach ($slots as $slot) {
                $start = Time::toMinutes($slot['start_time'] ?? null);
                $end = Time::toMinutes($slot['end_time'] ?? null);

                if ($start === null || $end === null || $end <= $start) {
                    continue;
                }

                $index[$id][$date][] = [
                    'start' => $start,
                    'end' => $end,
                    'candidate_count' => (int) collect($slot['candidate_groups'] ?? [])->sum('candidate_count'),
                    'invigilators' => array_values($slot['invigilators'] ?? []),
                ];
            }
        }

        return $index;
    }
}
