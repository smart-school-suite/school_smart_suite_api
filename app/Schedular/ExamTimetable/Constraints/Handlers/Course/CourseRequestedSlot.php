<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Course;

use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest as CourseRequestedSlotConstraint;
use App\Schedular\ExamTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\ExamTimetable\Constraints\Handlers\Concerns\PlacesRequestedSessions;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;

/** "Put this course's exam at this date and time." */
class CourseRequestedSlot implements ConstraintHandler
{
    use PlacesRequestedSessions;

    public static function supports(): string
    {
        return CourseRequestedSlotConstraint::KEY;
    }

    public static function stage(): string
    {
        return self::BEFORE_PLACEMENT;
    }

    public function handle(State $state): void
    {
        $context = RequestContext::fromPayload();

        foreach ($context->courseRequestedSlots() as $request) {
            if (!$context->hasCourse($request['course_id'] ?? '')) {
                continue;
            }

            $blockers = $this->placeRequestedSession($state, $request, self::supports());

            if ($blockers) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key' => self::supports(),
                        'course_id' => $request['course_id'],
                        'date' => $request['date'],
                        'start_time' => $request['start_time'],
                        'end_time' => $request['end_time'],
                    ],
                    'blockers' => array_values($blockers),
                ];
            }
        }
    }
}
