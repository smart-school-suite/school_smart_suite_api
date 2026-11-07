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
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Read access to the scheduler input.
 *
 * Input shape (all times "HH:MM", all dates "Y-m-d"):
 *
 *  exam_id, specialty_id, start_date, end_date, candidate_count,
 *  candidates_per_invigilator  (optional: one invigilator per N candidates in a hall, default one per hall)
 *  courses:                  [{course_id, course_title, course_code, candidate_count?}]
 *  halls:                    [{hall_id, hall_name, capacity}]
 *  invigilators:             [{invigilator_id, name, courses_taught: [course_id]}]
 *  hall_busy_periods:        [{hall_id, date, start_time, end_time, candidate_count?, source?}]
 *  invigilator_busy_periods: [{invigilator_id, date, start_time, end_time, source?}]
 *  hard_constraints:
 *    operational_period:            {start_time, end_time, date_exceptions: [{date, start_time, end_time}], date_exclusions: [date]}
 *    session_duration:              {duration, course_exceptions: [{course_id, duration}]}
 *    required_joint_course_periods: [{course_id, date, start_time, end_time}]
 *  soft_constraints:
 *    requested_assignment:       [{course_id, date, start_time, end_time, hall_ids: [], invigilator_ids: []}]
 *    course_requested_slot:      [{course_id, date, start_time, end_time}]
 *    hall_requested_slot:        [{hall_id, date, start_time, end_time}]
 *    invigilator_requested_slot: [{invigilator_id, date, start_time, end_time}]
 *    student_daily_load_range:   {min_sessions, max_sessions, date_exceptions: [{date, min_sessions, max_sessions}]}
 */
class RequestContext extends ExamTimetableContext
{
    public const DEFAULT_START_TIME = '08:00';
    public const DEFAULT_END_TIME = '17:00';
    public const DEFAULT_DURATION = 120;

    private array $courses;
    private array $halls;
    private array $invigilators;
    private array $teaching;

    private function __construct(private readonly array $payload)
    {
        $this->courses = collect($payload['courses'] ?? [])->keyBy('course_id')->all();
        $this->halls = collect($payload['halls'] ?? [])
            ->map(fn(array $hall) => [...$hall, 'capacity' => (int) ($hall['capacity'] ?? 0)])
            ->keyBy('hall_id')
            ->all();
        $this->invigilators = collect($payload['invigilators'] ?? [])->keyBy('invigilator_id')->all();
        $this->teaching = collect($this->invigilators)
            ->map(fn(array $invigilator) => array_flip($invigilator['courses_taught'] ?? $invigilator['course_taught'] ?? []))
            ->all();
    }

    public static function fromPayload(): self
    {
        return self::$context ??= new self(self::$requestPayload);
    }

    /** A context over any input, independent of the run in progress. */
    public static function for(array $payload): self
    {
        return new self($payload);
    }

    private function hard(string $key, mixed $default = []): mixed
    {
        return $this->payload['hard_constraints'][$key] ?? $default;
    }

    private function soft(string $key, mixed $default = []): mixed
    {
        return $this->payload['soft_constraints'][$key] ?? $default;
    }

    // ── Exam ──────────────────────────────────────────────────────────────

    public function examId(): ?string
    {
        return $this->payload['exam_id'] ?? null;
    }

    public function specialty(): ?string
    {
        return $this->payload['specialty_id'] ?? null;
    }

    public function examStartDate(): ?string
    {
        return $this->payload['start_date'] ?? null;
    }

    public function examEndDate(): ?string
    {
        return $this->payload['end_date'] ?? null;
    }

    public function candidateCount(): int
    {
        return (int) ($this->payload['candidate_count'] ?? 0);
    }

    public function candidateCountFor(string $courseId): int
    {
        $count = $this->courses[$courseId]['candidate_count'] ?? null;
        return $count !== null ? (int) $count : $this->candidateCount();
    }

    /** How many invigilators a hall seating $candidates needs. */
    public function invigilatorsNeeded(int $candidates): int
    {
        $ratio = (int) ($this->payload['candidates_per_invigilator'] ?? 0);
        if ($ratio <= 0) {
            return 1;
        }
        return max(1, (int) ceil($candidates / $ratio));
    }

    // ── Courses, halls and invigilators ──────────────────────────────────

    public function courses(): Collection
    {
        return collect(array_values($this->courses));
    }

    public function course(string $courseId): ?array
    {
        return $this->courses[$courseId] ?? null;
    }

    public function hasCourse(string $courseId): bool
    {
        return isset($this->courses[$courseId]);
    }

    public function halls(): Collection
    {
        return collect(array_values($this->halls));
    }

    public function hall(string $hallId): ?array
    {
        return $this->halls[$hallId] ?? null;
    }

    public function hallCapacity(string $hallId): int
    {
        return $this->halls[$hallId]['capacity'] ?? 0;
    }

    public function invigilators(): Collection
    {
        return collect(array_values($this->invigilators));
    }

    public function invigilator(string $invigilatorId): ?array
    {
        return $this->invigilators[$invigilatorId] ?? null;
    }

    /** True when the invigilator teaches the course, so must not invigilate its exam. */
    public function teaches(string $invigilatorId, string $courseId): bool
    {
        return isset($this->teaching[$invigilatorId][$courseId]);
    }

    /** @return string[] invigilators who teach the course */
    public function teachersOf(string $courseId): array
    {
        return collect($this->teaching)
            ->filter(fn(array $courses) => isset($courses[$courseId]))
            ->keys()
            ->all();
    }

    public function hallBusyPeriods(): array
    {
        return $this->payload['hall_busy_periods'] ?? [];
    }

    public function invigilatorBusyPeriods(): array
    {
        return $this->payload['invigilator_busy_periods'] ?? [];
    }

    // ── Operational period ───────────────────────────────────────────────

    public function operationalHours(): array
    {
        $period = $this->hard(OperationalPeriod::KEY);

        return [
            'start_time' => Time::normalize($period['start_time'] ?? null) ?? self::DEFAULT_START_TIME,
            'end_time' => Time::normalize($period['end_time'] ?? null) ?? self::DEFAULT_END_TIME,
        ];
    }

    public function dateExclusions(): array
    {
        return array_values($this->hard(OperationalPeriod::KEY)['date_exclusions'] ?? []);
    }

    public function dateExceptions(): Collection
    {
        return collect($this->hard(OperationalPeriod::KEY)['date_exceptions'] ?? []);
    }

    /** Opening hours of a date, or null when the date has no exams. */
    public function operationalHourDate(string $date): ?array
    {
        if (!$this->isOperationalDate($date)) {
            return null;
        }

        $exception = $this->dateExceptions()->firstWhere('date', $date);

        return $exception
            ? ['start_time' => Time::normalize($exception['start_time']), 'end_time' => Time::normalize($exception['end_time'])]
            : $this->operationalHours();
    }

    public function isWithinExamPeriod(string $date): bool
    {
        $start = $this->examStartDate();
        $end = $this->examEndDate();

        return $start && $end && $date >= $start && $date <= $end;
    }

    public function isOperationalDate(string $date): bool
    {
        return $this->isWithinExamPeriod($date) && !in_array($date, $this->dateExclusions(), true);
    }

    /** Every date of the exam period that can hold exams. */
    public function operationalDates(): array
    {
        $start = $this->examStartDate();
        $end = $this->examEndDate();

        if (!$start || !$end || $start > $end) {
            return [];
        }

        $excluded = array_flip($this->dateExclusions());
        $dates = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $formatted = $date->format('Y-m-d');
            if (!isset($excluded[$formatted])) {
                $dates[] = $formatted;
            }
        }

        return $dates;
    }

    // ── Session duration ─────────────────────────────────────────────────

    public function sessionDuration(): int
    {
        $duration = $this->hard(SessionDuration::KEY);
        return (int) ($duration['duration'] ?? $duration['duration_minutes'] ?? self::DEFAULT_DURATION);
    }

    public function sessionDurationCourse(string $courseId): int
    {
        $exception = collect($this->hard(SessionDuration::KEY)['course_exceptions'] ?? [])
            ->firstWhere('course_id', $courseId);

        return $exception
            ? (int) ($exception['duration'] ?? $exception['duration_minutes'])
            : $this->sessionDuration();
    }

    // ── Joint courses ────────────────────────────────────────────────────

    public function jointCourses(): Collection
    {
        return collect($this->hard(RequiredJointCourseSession::KEY))->map(fn(array $period) => [
            ...$period,
            'start_time' => Time::normalize($period['start_time'] ?? null),
            'end_time' => Time::normalize($period['end_time'] ?? null),
        ]);
    }

    // ── Soft requests ────────────────────────────────────────────────────

    public function requestedAssignments(): Collection
    {
        return collect($this->soft(RequestedAssignment::KEY))->map(fn(array $request) => [
            ...$this->normalizeWindow($request),
            'hall_ids' => array_values(array_filter($request['hall_ids'] ?? [$request['hall_id'] ?? null])),
            'invigilator_ids' => array_values(array_filter($request['invigilator_ids'] ?? [$request['invigilator_id'] ?? null])),
        ]);
    }

    public function courseRequestedSlots(): Collection
    {
        return collect($this->soft(CourseTimeRequest::KEY))->map(fn(array $request) => $this->normalizeWindow($request));
    }

    public function hallRequestedSlots(): Collection
    {
        return collect($this->soft(HallRequestedSlot::KEY))->map(fn(array $request) => $this->normalizeWindow($request));
    }

    public function invigilatorRequestedSlots(): Collection
    {
        return collect($this->soft(InvigilatorRequestedSlot::KEY))->map(fn(array $request) => $this->normalizeWindow($request));
    }

    /** Requests overlapping a window, used to rank preferred halls or invigilators. */
    public function requestsOverlapping(Collection $requests, string $date, int $start, int $end): Collection
    {
        return $requests->filter(fn(array $request) => ($request['date'] ?? null) === $date
            && $request['start_time'] && $request['end_time']
            && Time::overlaps($start, $end, Time::toMinutes($request['start_time']), Time::toMinutes($request['end_time'])));
    }

    // ── Student daily load ───────────────────────────────────────────────

    public function hasDailyLoadRange(): bool
    {
        return !empty($this->soft(StudentDailyLoadRange::KEY, null));
    }

    /** @return array{min_sessions: ?int, max_sessions: ?int} */
    public function dailyLoadFor(string $date): array
    {
        $range = $this->soft(StudentDailyLoadRange::KEY, null) ?? [];
        $exception = collect($range['date_exceptions'] ?? [])->firstWhere('date', $date);
        $source = $exception ?? $range;

        return [
            'min_sessions' => isset($source['min_sessions']) ? (int) $source['min_sessions'] : null,
            'max_sessions' => isset($source['max_sessions']) ? (int) $source['max_sessions'] : null,
        ];
    }

    private function normalizeWindow(array $request): array
    {
        return [
            ...$request,
            'start_time' => Time::normalize($request['start_time'] ?? null),
            'end_time' => Time::normalize($request['end_time'] ?? null),
        ];
    }
}
