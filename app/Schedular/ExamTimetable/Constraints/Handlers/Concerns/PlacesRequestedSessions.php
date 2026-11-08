<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Concerns;

use App\Schedular\ExamTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\CourseAlreadyScheduledValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\CourseRequestedSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Course\RequiredJointCourseSlotValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Schedule\SessionDurationValidator;
use App\Schedular\ExamTimetable\Constraints\Validator\Student\StudentDailyLoadValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\AllocationDTO;
use App\Schedular\ExamTimetable\Placement\Resources\ResourceAllocator;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/**
 * Shared by the requests that put a course's exam at a given time.
 * The request is checked against the rules and against what is already
 * placed; when nothing is in the way, halls and invigilators are found and
 * the session is placed. Otherwise the blockers explain why it was not.
 */
trait PlacesRequestedSessions
{
    /**
     * @return array[] blockers, empty when the session was placed
     */
    protected function placeRequestedSession(State $state, array $request, string $source, array $allocation = []): array
    {
        $params = [
            'course_id' => $request['course_id'],
            'date' => $request['date'],
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
            'slot_type' => $source,
        ];

        $blockers = [
            ...app(CourseAlreadyScheduledValidator::class)->check($params, $state),
            ...app(OperationalPeriodValidator::class)->check($params, $state),
            ...app(SessionDurationValidator::class)->check($params, $state),
            ...app(RequiredJointCourseSlotValidator::class)->check($params, $state),
            ...app(RequestedAssignmentValidator::class)->check($params, $state),
            ...app(CourseRequestedSlotValidator::class)->check($params, $state),
            ...app(StudentDailyLoadValidator::class)->check($params, $state),
        ];

        if ($blockers) {
            return $blockers;
        }

        $context = RequestContext::fromPayload();
        $start = Time::toMinutes($request['start_time']);
        $end = Time::toMinutes($request['end_time']);
        $allocator = app(ResourceAllocator::class);

        $result = $allocator->allocate($state, [
            ...$params,
            ...$allocation,
            'preferred_hall_ids' => $context->requestsOverlapping($context->hallRequestedSlots(), $request['date'], $start, $end)->pluck('hall_id')->all(),
            'preferred_invigilator_ids' => $context->requestsOverlapping($context->invigilatorRequestedSlots(), $request['date'], $start, $end)->pluck('invigilator_id')->all(),
        ]);

        if (!$result['ok']) {
            return $result['blockers'];
        }

        $session = [
            'type' => AllocationDTO::TYPE_REGULAR,
            'source' => $source,
            'course_id' => $request['course_id'],
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
            'candidate_count' => $context->candidateCountFor($request['course_id']),
            'halls' => $result['halls'],
        ];

        $state->addSession($request['date'], $session);
        $allocator->commit($state, $request['date'], $session);

        return [];
    }
}
