<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Context\RequestContext;
use Carbon\Carbon;

class OperationalPeriodValidator implements ValidatorInterface
{
    protected const OPERATIONAL_DATE = "operational_date";
    protected const OPERATIONAL_HOUR = "operational_hour";
    public function check(array $params): ?array
    {
        $context = RequestContext::fromPayload();
        $date = $params['date'];

        $start = Carbon::parse($params['start_time']);
        $end   = Carbon::parse($params['end_time']);

        $opWin   = $context->operationalHourDate($date);
        $opStart = Carbon::parse($opWin['start']);
        $opEnd   = Carbon::parse($opWin['end']);

        if (!$context->isOperationalDate($date)) {
            return  [
                "entity_type" => self::OPERATIONAL_DATE,
                "type" => "invalid_operational_date",
                "allowed_dates" => $context->operationalDates(),
                "conflict"   => array_filter([
                    "course_id"  => $params["course_id"] ?? null,
                    "start_time" => $params["start_time"] ?? null,
                    "end_time"   => $params["end_time"] ?? null,
                    "date"        => $params["date"] ?? null,
                    "slot_type"  => $params["slot_type"] ?? null,
                    "teacher_id" => $params["teacher_id"] ?? null,
                    "hall_id"    => $params["hall_id"] ?? null,
                ])
            ];
        }

        $opWin = $context->operationalHourDate($date);

        if ($opWin === null) {
            return [
                "entity_type" => self::OPERATIONAL_DATE,
                "type" => "date_excluded_from_operations",
                "date" => $date,
                "allowed_dates" => $context->operationalDates(),
                "conflict"   => array_filter([
                    "course_id"  => $params["course_id"] ?? null,
                    "start_time" => $params["start_time"] ?? null,
                    "end_time"   => $params["end_time"] ?? null,
                    "date"        => $params["date"] ?? null,
                    "slot_type"  => $params["slot_type"] ?? null,
                    "teacher_id" => $params["teacher_id"] ?? null,
                    "hall_id"    => $params["hall_id"] ?? null,
                ])
            ];
        }
        if ($start->lessThan($opStart) || $end->greaterThan($opEnd)) {
            return [
                'key'        => self::OPERATIONAL_HOUR,
                "type" => "invalid_operational_hour",
                'date'        => $date,
                "allowed_time" => [
                    'start_time' => $opWin['start'],
                    'end_time'   => $opWin['end'],
                ],
                "conflict"   => array_filter([
                    "course_id"  => $params["course_id"] ?? null,
                    "start_time" => $params["start_time"] ?? null,
                    "end_time"   => $params["end_time"] ?? null,
                    "day"        => $params["day"] ?? null,
                    "slot_type"  => $params["slot_type"] ?? null,
                    "teacher_id" => $params["teacher_id"] ?? null,
                    "hall_id"    => $params["hall_id"] ?? null,
                ])
            ];
        }



        return [];
    }
}
