<?php

namespace App\Schedular\ExamTimetable\Grid;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Schedule\OperationalPeriod as OperationalPeriodConstraint;
use App\Constant\Constraint\ExamTimetable\Schedule\SessionDuration as SessionDurationConstraint;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod as OperationalPeriodViolation;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\AllocationDTO;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Placement\Indexes\ExamPlacementIndex;
use App\Schedular\ExamTimetable\Placement\Resources\ResourceAllocator;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Checks the hard constraints, builds one day per exam date and places the
 * joint course exams, whose times are fixed.
 *
 * Any hard constraint that fails stops the run (HardConstraintFailureException):
 * without a valid exam period, opening hours, durations and joint courses
 * there is no timetable to build.
 */
class DayBuilder
{
    public function build(State $state): void
    {
        $context = RequestContext::fromPayload();

        $state->violations['hard'] = [
            ...$this->validateOperationalPeriod($context),
            ...$this->validateSessionDurations($context),
        ];

        if (!empty($state->violations['hard'])) {
            throw new HardConstraintFailureException();
        }

        $state->dateGrid = $this->generateDateGrid($context);
        $state->index = ExamPlacementIndex::fromContext($context);

        $this->buildJointCourses($state, $context);

        if (!empty($state->violations['hard'])) {
            throw new HardConstraintFailureException();
        }
    }

    protected function generateDateGrid(RequestContext $context): array
    {
        $grid = [];

        foreach ($context->operationalDates() as $date) {
            $hours = $context->operationalHourDate($date);
            $grid[$date] = new ExamDateDTO(
                date: $date,
                startTime: $hours['start_time'],
                endTime: $hours['end_time'],
                allocations: []
            );
        }

        return $grid;
    }

    protected function validateOperationalPeriod(RequestContext $context): array
    {
        $start = $context->examStartDate();
        $end = $context->examEndDate();
        $reasons = [];

        if (!$start || !$end || $start > $end) {
            return [$this->operationalFailure(null, [
                'key' => OperationalPeriodViolation::KEY,
                'entity_type' => OperationalPeriodValidator::OPERATIONAL_DATE,
                'type' => 'invalid_exam_period',
                'allowed_period' => ['start_date' => $start, 'end_date' => $end],
            ])];
        }

        if (empty($context->operationalDates())) {
            return [$this->operationalFailure(null, [
                'key' => OperationalPeriodViolation::KEY,
                'entity_type' => OperationalPeriodValidator::OPERATIONAL_DATE,
                'type' => 'no_exam_dates',
                'allowed_period' => ['start_date' => $start, 'end_date' => $end],
                'excluded_dates' => $context->dateExclusions(),
            ])];
        }

        foreach ($context->operationalDates() as $date) {
            $hours = $context->operationalHourDate($date);
            if (!$hours['start_time'] || !$hours['end_time'] || Time::toMinutes($hours['end_time']) <= Time::toMinutes($hours['start_time'])) {
                $reasons[] = $this->operationalFailure($date, [
                    'key' => OperationalPeriodViolation::KEY,
                    'entity_type' => OperationalPeriodValidator::OPERATIONAL_HOUR,
                    'type' => 'invalid_operational_hour',
                    'date' => $date,
                    'allowed_time' => $hours,
                ]);
            }
        }

        return $reasons;
    }

    /** Every course's exam must fit inside the opening hours of at least one date. */
    protected function validateSessionDurations(RequestContext $context): array
    {
        $longest = 0;
        $longestDate = null;

        foreach ($context->operationalDates() as $date) {
            $hours = $context->operationalHourDate($date);
            $span = Time::duration($hours['start_time'], $hours['end_time']);
            if ($span > $longest) {
                $longest = $span;
                $longestDate = $date;
            }
        }

        $reasons = [];

        foreach ($context->courses() as $course) {
            $duration = $context->sessionDurationCourse($course['course_id']);

            if ($duration > 0 && $duration <= $longest) {
                continue;
            }

            $reasons[] = [
                'constraint_failed' => [
                    'key' => SessionDurationConstraint::KEY,
                    'course_id' => $course['course_id'],
                    'duration' => $duration,
                ],
                'blockers' => [[
                    'key' => OperationalPeriodViolation::KEY,
                    'entity_type' => OperationalPeriodValidator::OPERATIONAL_HOUR,
                    'type' => $duration <= 0 ? 'invalid_duration' : 'duration_longer_than_opening_hours',
                    'date' => $longestDate,
                    'allowed_time' => $longestDate ? $context->operationalHourDate($longestDate) : null,
                    'longest_opening_minutes' => $longest,
                    'conflict' => ['course_id' => $course['course_id'], 'slot_type' => SessionDurationConstraint::KEY],
                ]],
            ];
        }

        return $reasons;
    }

    /**
     * Joint course exams are shared with other specialties, so their time is
     * fixed. Each one must be on an exam date, inside its opening hours, must
     * not overlap another joint course and must get halls and invigilators.
     */
    protected function buildJointCourses(State $state, RequestContext $context): void
    {
        $allocator = app(ResourceAllocator::class);

        foreach ($context->jointCourses() as $jointCourse) {
            $params = [
                'course_id' => $jointCourse['course_id'] ?? null,
                'date' => $jointCourse['date'] ?? null,
                'start_time' => $jointCourse['start_time'],
                'end_time' => $jointCourse['end_time'],
                'slot_type' => RequiredJointCourseSession::KEY,
            ];

            $blockers = app(OperationalPeriodValidator::class)->check($params, $state);

            if (empty($blockers)) {
                $start = Time::toMinutes($params['start_time']);
                $end = Time::toMinutes($params['end_time']);

                foreach ($state->sessionsOverlapping($params['date'], $start, $end, $params['course_id']) as $session) {
                    $blockers[] = [
                        'key' => RequiredJointCourse::KEY,
                        'course_id' => $session['course_id'],
                        'date' => $params['date'],
                        'start_time' => $session['start_time'],
                        'end_time' => $session['end_time'],
                        'conflict' => array_filter($params),
                    ];
                }
            }

            if (empty($blockers)) {
                $result = $allocator->allocate($state, $params);
                $blockers = $result['blockers'];
            }

            if (!empty($blockers)) {
                $state->violations['hard'][] = [
                    'constraint_failed' => array_filter([
                        'key' => RequiredJointCourseSession::KEY,
                        'course_id' => $params['course_id'],
                        'date' => $params['date'],
                        'start_time' => $params['start_time'],
                        'end_time' => $params['end_time'],
                    ]),
                    'blockers' => array_values($blockers),
                ];
                continue;
            }

            $session = [
                'type' => AllocationDTO::TYPE_JOINT,
                'source' => RequiredJointCourseSession::KEY,
                'course_id' => $params['course_id'],
                'start_time' => $params['start_time'],
                'end_time' => $params['end_time'],
                'candidate_count' => $context->candidateCountFor($params['course_id']),
                'halls' => $result['halls'],
            ];

            $state->addSession($params['date'], $session);
            $allocator->commit($state, $params['date'], $session);
        }
    }

    private function operationalFailure(?string $date, array $blocker): array
    {
        return [
            'constraint_failed' => array_filter([
                'key' => OperationalPeriodConstraint::KEY,
                'date' => $date,
            ]),
            'blockers' => [[...$blocker, 'conflict' => ['slot_type' => OperationalPeriodConstraint::KEY]]],
        ];
    }
}
