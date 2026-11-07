<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Handlers\Concerns\PlacesRequestedSessions;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;

/**
 * "Put this course's exam at this date and time, in these halls, with these
 * invigilators." Missing seats or invigilators are topped up automatically.
 */
class RequestedAssignment implements ConstraintHandler
{
    use PlacesRequestedSessions;

    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    public static function stage(): string
    {
        return self::BEFORE_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();

        foreach ($context->requestedAssignments() as $request) {
            if (!$context->hasCourse($request['course_id'] ?? '')) {
                continue;
            }

            $blockers = $this->placeRequestedSession($state, $request, self::supports(), array_filter([
                'hall_ids' => $request['hall_ids'] ?: null,
                'invigilator_ids' => $request['invigilator_ids'] ?: null,
            ]));

            if ($blockers) {
                $state->violations['soft'][] = [
                    'constraint_failed' => array_filter([
                        'key' => self::supports(),
                        'course_id' => $request['course_id'],
                        'date' => $request['date'],
                        'start_time' => $request['start_time'],
                        'end_time' => $request['end_time'],
                        'hall_ids' => $request['hall_ids'] ?: null,
                        'invigilator_ids' => $request['invigilator_ids'] ?: null,
                    ]),
                    'blockers' => array_values($blockers),
                ];
            }
        }
    }
}
