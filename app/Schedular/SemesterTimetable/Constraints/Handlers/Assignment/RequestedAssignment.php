<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\CourseRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\JointCoursePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\BreakPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\PeriodDurationValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\RequestedFreePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherUnavailableValidator;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\DTO\TimetableContext;

class RequestedAssignment extends TimetableContext implements ConstraintHandler
{
    public static function supports(): string
    {
        return RequestedAssignmentConstraint::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);

        foreach ($context->requestedAssignments() as $assignment) {
            $params = [
                'teacher_id' => $assignment['teacher_id'],
                'hall_id'    => $assignment['hall_id'],
                'course_id'  => $assignment['course_id'],
                'start_time' => $assignment['start_time'],
                'end_time'   => $assignment['end_time'],
                'day'        => $assignment['day'],
                'slot_type'  => RequestedAssignmentConstraint::KEY,
            ];

            $blockers = array_filter([
                self::isWithPreference() ? app(TeacherUnavailableValidator::class)->check($context, $params) : [],
                app(BreakPeriodValidator::class)->check($context, $params),
                app(OperationalPeriodValidator::class)->check($context, $params),
                app(PeriodDurationValidator::class)->check($context, $params),
                ...app(TeacherRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(HallBusyValidator::class)->check($context, $params),
                ...app(TeacherBusyValidator::class)->check($context, $params),
                ...app(JointCoursePeriodValidator::class)->check($context, $params),
                ...app(RequestedFreePeriodValidator::class)->check($context, $params),
                ...app(HallRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(CourseRequestedTimeSlotValidator::class)->check($context, $params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key'        => RequestedAssignmentConstraint::KEY,
                        'teacher_id' => $assignment['teacher_id'],
                        'hall_id'    => $assignment['hall_id'],
                        'course_id'  => $assignment['course_id'],
                        'start_time' => $assignment['start_time'],
                        'end_time'   => $assignment['end_time'],
                        'day'        => $assignment['day'],
                    ],
                    'blockers' => array_values($blockers),
                ];
                continue;
            }

            $this->enforce($state, $assignment);
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
