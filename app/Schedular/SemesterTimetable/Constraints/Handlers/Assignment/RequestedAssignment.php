<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\JointCoursePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\BreakPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\PeriodDurationValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherUnavailableValidator;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
class RequestedAssignment implements ConstraintHandler
{
    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        $requestedAssignments = $context->requestedAssignments();
        foreach ($requestedAssignments as $requestedAssignment) {
            $params = [
                'teacher_id' => $requestedAssignment["teacher_id"],
                'hall_id' => $requestedAssignment["hall_id"],
                "course_id" => $requestedAssignment["course_id"],
                "start_time" => $requestedAssignment["start_time"],
                "end_time" => $requestedAssignment["end_time"],
                "day" => $requestedAssignment["day"],
                "slot_type" => RequestedAssignmentConstraint::KEY
            ];
            $blockers = array_filter([
                [...app(TeacherBusyValidator::class)->check($context, $params)],
                [...app(TeacherUnavailableValidator::class)->check($context, $params)],
                [...app(BreakPeriodValidator::class)->check($context, $params)],
                [...app(OperationalPeriodValidator::class)->check($context, $params)],
                [...app(PeriodDurationValidator::class)->check($context, $params)],
                [...app(HallBusyValidator::class)->check($context, $params)],
                [...app(JointCoursePeriodValidator::class)->check($context, $params)],
            ]);
            if (!empty($blockers)) {
                $state->violations["soft"] = [
                    [
                        'constraint_failed' => [
                            'key'      => RequestedAssignmentConstraint::KEY,
                            'teacher_id' => $requestedAssignment["teacher_id"],
                            'hall_id' => $requestedAssignment["hall_id"],
                            "course_id" => $requestedAssignment["course_id"],
                            "start_time" => $requestedAssignment["start_time"],
                            "end_time" => $requestedAssignment["end_time"],
                            "day" => $requestedAssignment["day"],
                        ],
                        'blockers' => [
                            ...$blockers
                        ],
                    ]
                ];
                return;
            }
            $this->enforce($state, $requestedAssignment);
        }
    }

    public function enforce(State $state, $requestedAssignment): void
    {
        $day = $requestedAssignment["day"];
        $startTime = $requestedAssignment["start_time"];
        $endTime = $requestedAssignment["end_time"];

        $found = false;

        foreach ($state->grid as $slot) {
            if (
                $slot->day === $day &&
                $slot->start_time === $startTime &&
                $slot->end_time === $endTime
            ) {
                $slot->hall_id = $requestedAssignment['hall_id'];
                $slot->teacher_id = $requestedAssignment['teacher_id'];
                $slot->course_id = $requestedAssignment['course_id'];

                $found = true;
                break;
            }
        }

        if (!$found) {
            $gridSlotDto = new GridSlotDTO();
            $gridSlotDto->type = GridSlotDTO::TYPE_REGULAR;
            $gridSlotDto->day = $day;
            $gridSlotDto->start_time = $startTime;
            $gridSlotDto->end_time = $endTime;
            $gridSlotDto->hall_id = $requestedAssignment["hall_id"];
            $gridSlotDto->teacher_id = $requestedAssignment["teacher_id"];
            $gridSlotDto->course_id = $requestedAssignment["course_id"];

            $state->grid[] = $gridSlotDto;
        }
    }
}
