<?php

namespace App\Schedular\ExamTimetable\Constraints\Handlers\Hall;

use App\Constant\Constraint\ExamTimetable\Hall\HallRequestedSlot as HallRequestedSlotConstraint;
use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Hall\HallUnavailable;
use App\Schedular\ExamTimetable\Constraints\Handlers\Support\ReviewsResourceRequests;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

class HallRequestedSlot extends ReviewsResourceRequests
{
    public static function supports(): string
    {
        return HallRequestedSlotConstraint::KEY;
    }

    protected function field(): string
    {
        return 'hall_id';
    }

    protected function entries(State $state): array
    {
        return $state->context->hallRequestedSlots();
    }

    protected function resourceBlockers(State $state, array $params): array
    {
        $id = $params['hall_id'];

        if (!$state->context->hall($id)) {
            return [[
                'key' => HallUnavailable::KEY,
                'hall_id' => $id,
                'reason' => 'not_an_exam_hall',
                'date' => $params['date'],
                'conflict' => ['slot_type' => static::supports(), 'hall_id' => $id, 'date' => $params['date']],
            ]];
        }

        foreach ($state->context->hallBusy($id, $params['date']) as $slot) {
            if (Time::overlaps($params['start'], $params['end'], $slot['start'], $slot['end'])) {
                return [[
                    'key' => HallBusy::KEY,
                    'hall_id' => $id,
                    'date' => $params['date'],
                    'start_time' => Time::toTime($slot['start']),
                    'end_time' => Time::toTime($slot['end']),
                    'conflict' => [
                        'slot_type' => static::supports(),
                        'hall_id' => $id,
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
