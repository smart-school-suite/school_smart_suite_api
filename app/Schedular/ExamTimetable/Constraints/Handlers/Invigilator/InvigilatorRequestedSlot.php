<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Invigilator;

use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot as InvigilatorRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorBusy;
use App\Constant\Violation\ExamTimetable\Invigilator\InvigilatorUnavailable;
use App\Schedular\ExamTimetable\Constraints\Handlers\Support\ReviewsResourceRequests;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

class InvigilatorRequestedSlot extends ReviewsResourceRequests
{
    public static function supports(): string
    {
        return InvigilatorRequestedSlotConstraint::KEY;
    }

    protected function field(): string
    {
        return 'invigilator_id';
    }

    protected function entries(State $state): array
    {
        return $state->context->invigilatorRequestedSlots();
    }

    protected function resourceBlockers(State $state, array $params): array
    {
        $id = $params['invigilator_id'];

        if (!$state->context->invigilator($id)) {
            return [[
                'key' => InvigilatorUnavailable::KEY,
                'invigilator_id' => $id,
                'reason' => 'not_assigned_to_exam',
                'date' => $params['date'],
                'conflict' => ['slot_type' => static::supports(), 'invigilator_id' => $id, 'date' => $params['date']],
            ]];
        }

        foreach ($state->context->invigilatorBusy($id, $params['date']) as $slot) {
            if (Time::overlaps($params['start'], $params['end'], $slot['start'], $slot['end'])) {
                return [[
                    'key' => InvigilatorBusy::KEY,
                    'invigilator_id' => $id,
                    'reason' => 'busy',
                    'date' => $params['date'],
                    'start_time' => Time::toTime($slot['start']),
                    'end_time' => Time::toTime($slot['end']),
                    'conflict' => [
                        'slot_type' => static::supports(),
                        'invigilator_id' => $id,
                        'date' => $params['date'],
                        'start_time' => Time::toTime($params['start']),
                        'end_time' => Time::toTime($params['end']),
                    ],
                ]];
            }
        }

        return [];
    }
}
