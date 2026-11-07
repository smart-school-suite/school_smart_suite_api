<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BaseValidator;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Placement\Support\Time;

/** The request must fall on an exam date and inside that date's opening hours. */
class OperationalPeriodValidator extends BaseValidator
{
    public const OPERATIONAL_DATE = "operational_date";
    public const OPERATIONAL_HOUR = "operational_hour";

    public function check(array $params, State $state): array
    {
        $context = RequestContext::fromPayload();
        $date = $params['date'] ?? '';

        if (!$context->isWithinExamPeriod($date)) {
            return [[
                'key' => OperationalPeriod::KEY,
                'entity_type' => self::OPERATIONAL_DATE,
                'type' => 'outside_exam_period',
                'date' => $date,
                'allowed_period' => ['start_date' => $context->examStartDate(), 'end_date' => $context->examEndDate()],
                'conflict' => $this->conflict($params),
            ]];
        }

        $window = $context->operationalHourDate($date);
        if ($window === null) {
            return [[
                'key' => OperationalPeriod::KEY,
                'entity_type' => self::OPERATIONAL_DATE,
                'type' => 'date_excluded_from_operations',
                'date' => $date,
                'conflict' => $this->conflict($params),
            ]];
        }

        if (empty($params['start_time']) || empty($params['end_time'])) {
            return [];
        }

        $start = Time::toMinutes($params['start_time']);
        $end = Time::toMinutes($params['end_time']);

        if ($end <= $start || $start < Time::toMinutes($window['start_time']) || $end > Time::toMinutes($window['end_time'])) {
            return [[
                'key' => OperationalPeriod::KEY,
                'entity_type' => self::OPERATIONAL_HOUR,
                'type' => 'invalid_operational_hour',
                'date' => $date,
                'allowed_time' => $window,
                'conflict' => $this->conflict($params),
            ]];
        }

        return [];
    }
}
