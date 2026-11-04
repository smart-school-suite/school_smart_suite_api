<?php

namespace App\Schedular\ExamTimetable\Placement;

use App\Constant\Constraint\ExamTimetable\Course\CourseExamSession;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamSessionDTO;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * Gives every course that is not already pinned one exam session.
 *
 * Courses are placed hardest first (longest exam, then most candidates). For each course the dates are tried
 * from least to most busy, keeping each date within the student daily maximum. Only when no date works
 * within the maximum is it exceeded (a soft violation reported by StudentDailyLoadRange). Within a date the
 * free time is filled best-fit (see windows()), and a time is taken when the halls seat every candidate and
 * each hall has an invigilator. A course that fits nowhere is a hard violation that
 * lists why each date failed.
 */
class PlacementEngine
{
    private const NO_TIME = 'no_time';
    private const NO_HALL = 'no_hall';
    private const NO_INVIGILATOR = 'no_invigilator';

    public function __construct(
        private HallAvailability $halls,
        private InvigilatorAvailability $invigilators,
        private Preferences $preferences,
    ) {}

    public function run(State $state): void
    {
        $context = $state->context;

        // Joint courses only ever sit at their joint session (DayBuilder); a failed one is reported there.
        $jointIds = array_column($context->jointCourses(), 'course_id');

        $courses = $context->courses()
            ->reject(fn(array $course) => $state->isScheduled($course['course_id']) || in_array($course['course_id'], $jointIds, true))
            ->sortBy([
                fn($a, $b) => $context->duration($b['course_id']) <=> $context->duration($a['course_id']),
                fn($a, $b) => $context->candidateCount($b['course_id']) <=> $context->candidateCount($a['course_id']),
                fn($a, $b) => strcmp((string) ($a['course_name'] ?? $a['course_id']), (string) ($b['course_name'] ?? $b['course_id'])),
            ])
            ->values();

        foreach ($courses as $course) {
            $this->placeCourse($state, $course['course_id']);
        }
    }

    private function placeCourse(State $state, string $courseId): void
    {
        $failures = [];

        foreach ([true, false] as $withinLimit) {
            foreach ($this->orderedDates($state, $withinLimit) as $date) {
                if (isset($failures[$date])) {
                    continue;
                }

                $outcome = $this->tryDate($state, $courseId, $date);

                if ($outcome instanceof ExamSessionDTO) {
                    $state->place($outcome);
                    return;
                }

                $failures[$date] = $outcome;
            }
        }

        $this->recordUnplaced($state, $courseId, $failures);
    }

    /** Dates under the daily maximum first (filling dates still below the minimum), or the remaining dates. */
    private function orderedDates(State $state, bool $withinLimit): array
    {
        return collect(array_keys($state->dateGrid))
            ->filter(function (string $date) use ($state, $withinLimit) {
                [, $max] = $state->context->dailyLoad($date);
                $under = $max === null || $state->load($date) < $max;

                return $withinLimit ? $under : !$under;
            })
            ->sortBy([
                function ($a, $b) use ($state) {
                    return $this->belowMinimum($state, $b) <=> $this->belowMinimum($state, $a);
                },
                fn($a, $b) => $state->load($a) <=> $state->load($b),
                fn($a, $b) => strcmp($a, $b),
            ])
            ->values()
            ->all();
    }

    private function belowMinimum(State $state, string $date): bool
    {
        [$min] = $state->context->dailyLoad($date);
        $load = $state->load($date);

        return $min !== null && $load > 0 && $load < $min;
    }

    private function tryDate(State $state, string $courseId, string $date): array|ExamSessionDTO
    {
        $context = $state->context;
        $duration = $context->duration($courseId);
        $candidates = $context->candidateCount($courseId);
        $windows = $this->windows($state, $date, $duration);

        if (empty($windows)) {
            return ['reason' => self::NO_TIME];
        }

        $bestSeats = 0;
        $reachedStaffing = false;

        foreach ($windows as [$start, $end]) {
            $halls = $this->halls->allocate($state, $date, $start, $end, $candidates, $this->preferences->halls($state, $date, $start, $end));

            if ($halls === null) {
                $bestSeats = max($bestSeats, $this->halls->seatsAvailable($state, $date, $start, $end));
                continue;
            }

            $reachedStaffing = true;
            $staffed = $this->invigilators->staff($state, $halls, $courseId, $date, $start, $end, $this->preferences->invigilators($state, $date, $start, $end));

            if ($staffed === null) {
                continue;
            }

            return new ExamSessionDTO(
                type: ExamSessionDTO::TYPE_REGULAR,
                courseId: $courseId,
                date: $date,
                start: $start,
                end: $end,
                candidateCount: $candidates,
                halls: $staffed,
            );
        }

        return $reachedStaffing
            ? ['reason' => self::NO_INVIGILATOR]
            : ['reason' => self::NO_HALL, 'seats_available' => $bestSeats];
    }

    /**
     * Start times on the date where the students are free, best first.
     *
     * Best fit: the smallest free stretch that holds the exam is used first, starting right after the exam
     * before it (or ending right before the exam after it), so the day is not cut into stretches too short
     * for the remaining exams. Every other start time follows as a fallback for when halls or invigilators
     * are not free at the preferred one.
     *
     * @return array[] [[start, end], ...]
     */
    private function windows(State $state, string $date, int $duration): array
    {
        $day = $state->dateGrid[$date];
        $gap = $state->context->sessionGap();
        $step = $state->context->slotStep();
        $windows = [];

        foreach ($this->freeStretches($state, $date) as $index => [$from, $to]) {
            $first = (int) (ceil($from / $step) * $step);
            $last = (int) (floor(($to - $duration) / $step) * $step);

            if ($first > $last) {
                continue;
            }

            $starts = array_values(array_unique([$first, $last, ...range($first, $last, $step)]));

            foreach ($starts as $rank => $start) {
                $windows[] = [
                    'window' => [$start, $start + $duration],
                    'order' => [$rank < 2 ? 0 : 1, $to - $from, $rank < 2 ? $rank : 2, $start],
                ];
            }
        }

        usort($windows, fn($a, $b) => $a['order'] <=> $b['order']);

        return array_column($windows, 'window');
    }

    /** Free stretches of the date for the students, keeping the gap around existing exams. */
    private function freeStretches(State $state, string $date): array
    {
        $day = $state->dateGrid[$date];
        $gap = $state->context->sessionGap();
        $cursor = $day->start;
        $stretches = [];

        foreach ($state->sessionsOn($date) as $session) {
            if ($session->start - $gap > $cursor) {
                $stretches[] = [$cursor, $session->start - $gap];
            }

            $cursor = max($cursor, $session->end + $gap);
        }

        if ($day->end > $cursor) {
            $stretches[] = [$cursor, $day->end];
        }

        return $stretches;
    }

    private function recordUnplaced(State $state, string $courseId, array $failures): void
    {
        $context = $state->context;
        $byReason = collect($failures)->map(fn($failure, $date) => [...$failure, 'date' => $date])->groupBy('reason');
        $conflict = ['slot_type' => CourseExamSession::KEY, 'course_id' => $courseId];
        $blockers = [];

        if (empty($failures)) {
            $blockers[] = [
                'key' => OperationalPeriod::KEY,
                'reason' => 'no_exam_dates',
                'allowed_start_date' => $context->startDate(),
                'allowed_end_date' => $context->endDate(),
                'conflict' => $conflict,
            ];
        }

        if ($byReason->has(self::NO_TIME)) {
            $dates = $byReason[self::NO_TIME]->pluck('date')->values()->all();
            $blockers[] = [
                'key' => OperationalPeriod::KEY,
                'reason' => 'no_free_time',
                'date' => $dates[0],
                'dates' => $dates,
                'duration' => $context->duration($courseId),
                'conflict' => $conflict,
            ];
        }

        if ($byReason->has(self::NO_HALL)) {
            $dates = $byReason[self::NO_HALL]->pluck('date')->values()->all();
            $blockers[] = [
                'key' => HallUnavailable::KEY,
                'date' => $dates[0],
                'dates' => $dates,
                'seats_needed' => $context->candidateCount($courseId),
                'seats_available' => (int) $byReason[self::NO_HALL]->max('seats_available'),
                'conflict' => $conflict,
            ];
        }

        if ($byReason->has(self::NO_INVIGILATOR)) {
            $dates = $byReason[self::NO_INVIGILATOR]->pluck('date')->values()->all();
            $blockers[] = [
                'key' => InvigilatorUnavailable::KEY,
                'date' => $dates[0],
                'dates' => $dates,
                'invigilators_available' => 0,
                'conflict' => $conflict,
            ];
        }

        $state->addViolation('hard', [
            'constraint_failed' => [
                'key' => CourseExamSession::KEY,
                'course_id' => $courseId,
                'duration' => $context->duration($courseId),
                'candidate_count' => $context->candidateCount($courseId),
            ],
            'blockers' => $blockers,
        ]);
    }
}
