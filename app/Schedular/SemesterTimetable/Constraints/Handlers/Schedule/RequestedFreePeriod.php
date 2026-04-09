<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as ScheduleRequestedFreePeriod;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\JointCoursePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\BreakPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\PeriodDurationValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;

class RequestedFreePeriod implements ConstraintHandler
{
    public static function supports(): string
    {
        return ScheduleRequestedFreePeriod::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        foreach ($context->requestedFreePeriods() as $rFreePeriod) {
            $params = [
                "start_time" => $rFreePeriod['start_time'],
                "end_time" => $rFreePeriod['end_time'],
                "day" => $rFreePeriod['day'],
            ];
            $blockers = array_filter([
                app(BreakPeriodValidator::class)->check($context, $params),
                app(OperationalPeriodValidator::class)->check($context, $params),
                app(PeriodDurationValidator::class)->check($context, $params),
                ...app(TeacherRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(HallBusyValidator::class)->check($context, $params),
                ...app(JointCoursePeriodValidator::class)->check($context, $params),
                ...app(RequestedAssignmentValidator::class)->check($context, $params),
                ...app(HallRequestedTimeSlotValidator::class)->check($context, $params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key'        => ScheduleRequestedFreePeriod::KEY,
                        'start_time' => $rFreePeriod['start_time'],
                        'end_time'   => $rFreePeriod['end_time'],
                        'day'        => $rFreePeriod['day'],
                    ],
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
            $this->enforce($state, $rFreePeriod);
        }
    }

    private function enforce(State $state, $rFreePeriod): void
    {
        $day = $rFreePeriod["day"];
        $startTime = $rFreePeriod["start_time"];
        $endTime = $rFreePeriod["end_time"];

        $found = false;

        foreach ($state->grid as $slot) {
            if (
                $slot->day === $day &&
                $slot->start_time === $startTime &&
                $slot->end_time === $endTime
            ) {
                $slot->type = GridSlotDTO::TYPE_FREE;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $gridSlotDto = new GridSlotDTO();
            $gridSlotDto->type = GridSlotDTO::TYPE_FREE;
            $gridSlotDto->day = $day;
            $gridSlotDto->start_time = $startTime;
            $gridSlotDto->end_time = $endTime;

            $state->grid[] = $gridSlotDto;
        }
    }
}
