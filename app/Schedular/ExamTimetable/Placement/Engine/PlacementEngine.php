<?php

namespace App\Schedular\ExamTimetable\Placement\Engine;

use App\Constant\Constraint\ExamTimetable\Course\CoursePlacement;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorTeachesCourse;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\ExamTimetable\Student\CandidateBusy;
use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\AllocationDTO;
use App\Schedular\ExamTimetable\Placement\Resources\ResourceAllocator;
use App\Schedular\ExamTimetable\Placement\Scoring\ExamDateScoring;
use App\Schedular\ExamTimetable\Placement\Scoring\ExamSlotScore;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Gives every course not placed yet one exam session.
 *
 * Courses go hardest first: those that fit on the fewest dates (long exams),
 * then those whose teachers make up more of the invigilators (fewer people can
 * invigilate them), then the longest exams.
 * For each course the dates are tried in ExamDateScoring order and, on each
 * date, the start times in ExamSlotScore order. The first time where
 * ResourceAllocator finds halls and invigilators wins.
 *
 * Dates already at the daily maximum are only tried when no other date works;
 * using one is recorded as a student daily load violation. A course that fits
 * nowhere is recorded as a hard course placement violation with the reasons.
 */
class PlacementEngine
{
    public function run(State $state): void
    {
        $context = RequestContext::fromPayload();

        foreach ($this->orderCourses($state, $context) as $course) {
            $courseId = $course['course_id'];
            if ($state->isPlaced($courseId)) {
                continue;
            }

            $this->placeCourse($state, $context, $courseId);
        }
    }

    private function orderCourses(State $state, RequestContext $context): array
    {
        return $context->courses()
            ->sortBy(fn(array $course) => [
                $this->datesLongEnough($state, $context->sessionDurationCourse($course['course_id'])),
                -count($context->teachersOf($course['course_id'])),
                -$context->sessionDurationCourse($course['course_id']),
                $course['course_title'] ?? $course['course_id'],
            ])
            ->values()
            ->all();
    }

    /** Dates whose opening hours can hold an exam this long. */
    private function datesLongEnough(State $state, int $duration): int
    {
        return count(array_filter(
            $state->dateGrid,
            fn($day) => Time::duration($day->startTime, $day->endTime) >= $duration
        ));
    }

    private function placeCourse(State $state, RequestContext $context, string $courseId): void
    {
        $duration = $context->sessionDurationCourse($courseId);
        $allocator = app(ResourceAllocator::class);
        $dates = app(ExamDateScoring::class)->rank($state, $courseId);
        $failures = ['times_tried' => 0, 'hall' => null, 'invigilator' => null];

        // Dates within the daily maximum first; over-full dates only as a last resort.
        foreach ([false, true] as $overflow) {
            foreach ($dates as $entry) {
                if ($entry['overflow'] !== $overflow) {
                    continue;
                }

                $date = $entry['date'];
                $slots = app(ExamSlotScore::class)->suggestSlots($state->dateGrid[$date], $duration, $courseId);

                foreach ($slots as $slot) {
                    $failures['times_tried']++;
                    $start = Time::toMinutes($slot['start_time']);
                    $end = Time::toMinutes($slot['end_time']);

                    $result = $allocator->allocate($state, [
                        'course_id' => $courseId,
                        'date' => $date,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'slot_type' => CoursePlacement::KEY,
                        'preferred_hall_ids' => $context->requestsOverlapping($context->hallRequestedSlots(), $date, $start, $end)->pluck('hall_id')->all(),
                        'preferred_invigilator_ids' => $context->requestsOverlapping($context->invigilatorRequestedSlots(), $date, $start, $end)->pluck('invigilator_id')->all(),
                    ]);

                    if (!$result['ok']) {
                        $this->rememberFailure($failures, $result['blockers']);
                        continue;
                    }

                    $session = [
                        'type' => AllocationDTO::TYPE_REGULAR,
                        'source' => AllocationDTO::SOURCE_PLACEMENT,
                        'course_id' => $courseId,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'candidate_count' => $context->candidateCountFor($courseId),
                        'halls' => $result['halls'],
                    ];

                    if ($overflow) {
                        $this->recordDailyLoadBreach($state, $context, $date, $courseId);
                    }

                    $state->addSession($date, $session);
                    $allocator->commit($state, $date, $session);
                    return;
                }
            }
        }

        $state->violations['hard'][] = [
            'constraint_failed' => [
                'key' => CoursePlacement::KEY,
                'course_id' => $courseId,
                'duration' => $duration,
                'candidate_count' => $context->candidateCountFor($courseId),
            ],
            'blockers' => $this->placementBlockers($context, $courseId, $duration, $failures),
        ];
    }

    /** Keeps the closest miss of each kind, to explain an unplaced course. */
    private function rememberFailure(array &$failures, array $blockers): void
    {
        foreach ($blockers as $blocker) {
            if ($blocker['key'] === HallUnavailable::KEY) {
                if (!$failures['hall'] || $blocker['seats_free'] > $failures['hall']['seats_free']) {
                    $failures['hall'] = $blocker;
                }
            }
            if ($blocker['key'] === InvigilatorUnavailable::KEY) {
                if (!$failures['invigilator'] || $blocker['invigilators_free'] > $failures['invigilator']['invigilators_free']) {
                    $failures['invigilator'] = $blocker;
                }
            }
        }
    }

    private function placementBlockers(RequestContext $context, string $courseId, int $duration, array $failures): array
    {
        $conflict = ['course_id' => $courseId, 'slot_type' => CoursePlacement::KEY];

        $candidateBusy = $this->candidateBusyBlocker($context, $courseId, $conflict);

        if ($failures['times_tried'] === 0) {
            if ($candidateBusy) {
                return [$candidateBusy];
            }
            return [[
                'key' => OperationalPeriod::KEY,
                'entity_type' => 'operational_hour',
                'type' => 'no_time_left',
                'duration' => $duration,
                'conflict' => $conflict,
            ]];
        }

        $blockers = [];

        if ($failures['hall']) {
            $blockers[] = $failures['hall'];
        }

        if ($failures['invigilator']) {
            $blockers[] = $failures['invigilator'];

            $teachers = $failures['invigilator']['teaching_invigilator_ids'] ?? [];
            if ($teachers) {
                $blockers[] = [
                    'key' => InvigilatorTeachesCourse::KEY,
                    'course_id' => $courseId,
                    'invigilator_ids' => $teachers,
                    'conflict' => $conflict,
                ];
            }
        }

        if ($candidateBusy) {
            $blockers[] = $candidateBusy;
        }

        return $blockers;
    }

    /** When some of the course's candidates sit other exams in the period, those times were off limits. */
    private function candidateBusyBlocker(RequestContext $context, string $courseId, array $conflict): ?array
    {
        $periods = [];
        foreach ($context->candidateBusyPeriodsOf($courseId) as $date => $windows) {
            foreach ($windows as $window) {
                $periods[] = [
                    'date' => $date,
                    'start_time' => Time::toTime($window['start']),
                    'end_time' => Time::toTime($window['end']),
                    'student_count' => $window['students'],
                ];
            }
        }

        if (!$periods) {
            return null;
        }

        return [
            'key' => CandidateBusy::KEY,
            'course_id' => $courseId,
            'student_count' => max(array_column($periods, 'student_count')),
            'busy_periods' => array_slice($periods, 0, 20),
            'conflict' => $conflict,
        ];
    }

    private function recordDailyLoadBreach(State $state, RequestContext $context, string $date, string $courseId): void
    {
        $max = $context->dailyLoadFor($date)['max_sessions'];

        foreach ($state->violations['soft'] as &$violation) {
            $failed = $violation['constraint_failed'];
            if ($failed['key'] === StudentDailyLoadRange::KEY && $failed['date'] === $date && $failed['breach'] === 'max') {
                $violation['constraint_failed']['sessions']++;
                $violation['blockers'][0]['course_ids'][] = $courseId;
                return;
            }
        }
        unset($violation);

        $state->violations['soft'][] = [
            'constraint_failed' => [
                'key' => StudentDailyLoadRange::KEY,
                'date' => $date,
                'breach' => 'max',
                'max_sessions' => $max,
                'sessions' => $state->load($date, $courseId) + 1,
            ],
            'blockers' => [[
                'key' => StudentDailyLoad::KEY,
                'date' => $date,
                'breach' => 'max',
                'max_sessions' => $max,
                'sessions' => $state->load($date, $courseId),
                'course_ids' => [...array_column($state->sessions($date, $courseId), 'course_id'), $courseId],
                'conflict' => ['course_id' => $courseId, 'date' => $date, 'slot_type' => CoursePlacement::KEY],
            ]],
        ];
    }
}
