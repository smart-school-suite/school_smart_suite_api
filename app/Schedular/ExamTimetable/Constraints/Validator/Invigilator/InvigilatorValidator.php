<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Invigilator;

use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Support\Time;

/**
 * With an invigilator named in the request: that person must be free.
 * Without one: at least one invigilator must be free.
 */
class InvigilatorValidator implements ValidatorInterface
{
    use BuildsConflict;

    public function __construct(private InvigilatorAvailability $invigilators) {}

    public function check(State $state, array $params): array
    {
        $invigilatorId = $params['invigilator_id'] ?? null;

        if ($invigilatorId) {
            $known = $state->context->invigilator($invigilatorId) !== null;

            if ($known && $this->invigilators->isFree($state, $invigilatorId, $params['date'], $params['start'], $params['end'])) {
                return [];
            }

            return [[
                'key' => $known ? InvigilatorBusy::KEY : InvigilatorUnavailable::KEY,
                'invigilator_id' => $invigilatorId,
                'reason' => $known ? 'busy' : 'not_assigned_to_exam',
                'date' => $params['date'],
                'start_time' => Time::toTime($params['start']),
                'end_time' => Time::toTime($params['end']),
                'conflict' => $this->conflict($params),
            ]];
        }

        $free = $this->invigilators->available($state, $params['course_id'] ?? null, $params['date'], $params['start'], $params['end']);

        return !empty($free) ? [] : [[
            'key' => InvigilatorUnavailable::KEY,
            'date' => $params['date'],
            'start_time' => Time::toTime($params['start']),
            'end_time' => Time::toTime($params['end']),
            'invigilators_needed' => 1,
            'invigilators_available' => 0,
            'conflict' => $this->conflict($params),
        ]];
    }
}
