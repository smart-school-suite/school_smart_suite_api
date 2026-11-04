<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Hall;

use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Hall\HallCapacityExceeded;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * With a hall named in the request: that hall must be free and large enough.
 * Without one: the free halls together must seat every candidate.
 */
class HallValidator implements ValidatorInterface
{
    use BuildsConflict;

    public function __construct(private HallAvailability $halls) {}

    public function check(State $state, array $params): array
    {
        $candidates = $state->context->candidateCount($params['course_id'] ?? null);
        $hallId = $params['hall_id'] ?? null;

        if (!$hallId) {
            $seats = $this->halls->seatsAvailable($state, $params['date'], $params['start'], $params['end']);

            return $seats >= max(1, $candidates) ? [] : [[
                'key' => HallUnavailable::KEY,
                'date' => $params['date'],
                'start_time' => Time::toTime($params['start']),
                'end_time' => Time::toTime($params['end']),
                'seats_needed' => $candidates,
                'seats_available' => $seats,
                'conflict' => $this->conflict($params),
            ]];
        }

        $hall = $state->context->hall($hallId);

        if (!$hall) {
            return [[
                'key' => HallUnavailable::KEY,
                'hall_id' => $hallId,
                'date' => $params['date'],
                'reason' => 'not_an_exam_hall',
                'seats_needed' => $candidates,
                'seats_available' => 0,
                'conflict' => $this->conflict($params),
            ]];
        }

        $status = $this->halls->status($state, $hall, $params['date'], $params['start'], $params['end']);

        if ($status['state'] === HallAvailability::BUSY) {
            $busy = $status['busy_with'] ?? [];

            return [[
                'key' => HallBusy::KEY,
                'hall_id' => $hallId,
                'date' => $params['date'],
                'start_time' => isset($busy['start']) ? Time::toTime($busy['start']) : Time::toTime($params['start']),
                'end_time' => isset($busy['end']) ? Time::toTime($busy['end']) : Time::toTime($params['end']),
                'busy_course_id' => $busy['course_id'] ?? null,
                'conflict' => $this->conflict($params),
            ]];
        }

        if ($status['remaining'] < $candidates) {
            return [[
                'key' => HallCapacityExceeded::KEY,
                'hall_id' => $hallId,
                'date' => $params['date'],
                'capacity' => (int) $hall['capacity'],
                'seats_available' => $status['remaining'],
                'seats_needed' => $candidates,
                'conflict' => $this->conflict($params),
            ]];
        }

        return [];
    }
}
