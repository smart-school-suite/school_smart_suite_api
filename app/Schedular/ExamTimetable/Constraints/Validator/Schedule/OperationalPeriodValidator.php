<?php

namespace App\Schedular\ExamTimetable\Constraints\Validator\Schedule;

use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\BuildsConflict;
use App\Schedular\ExamTimetable\Constraints\Validator\Contracts\ValidatorInterface;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\Support\Time;

class OperationalPeriodValidator implements ValidatorInterface
{
    use BuildsConflict;

    public const NOT_EXAM_DATE = 'not_exam_date';
    public const EXCLUDED_DATE = 'excluded_date';
    public const OUTSIDE_HOURS = 'outside_hours';

    public function check(State $state, array $params): array
    {
        $context = $state->context;
        $date = $params['date'];

        if (!$context->isInExamWindow($date)) {
            return [$this->blocker(self::NOT_EXAM_DATE, $params, [
                'allowed_start_date' => $context->startDate(),
                'allowed_end_date' => $context->endDate(),
            ])];
        }

        if (!$context->isOperationalDate($date)) {
            return [$this->blocker(self::EXCLUDED_DATE, $params)];
        }

        [$open, $close] = $context->window($date);

        if ($params['start'] < $open || $params['end'] > $close) {
            return [$this->blocker(self::OUTSIDE_HOURS, $params, [
                'start_time' => Time::toTime($open),
                'end_time' => Time::toTime($close),
            ])];
        }

        return [];
    }

    private function blocker(string $reason, array $params, array $details = []): array
    {
        return [
            'key' => OperationalPeriod::KEY,
            'reason' => $reason,
            'date' => $params['date'],
            ...$details,
            'conflict' => $this->conflict($params),
        ];
    }
}
