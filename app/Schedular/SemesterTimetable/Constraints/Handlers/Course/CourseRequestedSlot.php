<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Course;

use App\Constant\Constraint\SemesterTimetable\Course\CourseRequestedSlot as CourseRequestedSlotConstraint;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
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
use App\Schedular\SemesterTimetable\Helpers\GetHallByAvailabilityScore;

class CourseRequestedSlot implements ConstraintHandler
{
    public static function supports(): string
    {
        return CourseRequestedSlotConstraint::KEY;
    }
    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        foreach ($context->cRequestedWindows() as $cRequestedWindow) {
            $params = [
                'course_id'  => $cRequestedWindow['course_id'],
                'start_time' => $cRequestedWindow['start_time'],
                'end_time'   => $cRequestedWindow['end_time'],
                'day'        => $cRequestedWindow['day'],
                'slot_type'  => CourseRequestedSlotConstraint::KEY,
            ];

            $blockers = array_filter([
                app(TeacherUnavailableValidator::class)->check($context, $params),
                app(BreakPeriodValidator::class)->check($context, $params),
                app(OperationalPeriodValidator::class)->check($context, $params),
                app(PeriodDurationValidator::class)->check($context, $params),
                ...app(RequestedFreePeriodValidator::class)->check($context, $params),
                ...app(TeacherRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(HallBusyValidator::class)->check($context, $params),
                ...app(TeacherBusyValidator::class)->check($context, $params),
                ...app(JointCoursePeriodValidator::class)->check($context, $params),
                ...app(RequestedAssignmentValidator::class)->check($context, $params),
                ...app(HallRequestedTimeSlotValidator::class)->check($context, $params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key'        => CourseRequestedSlotConstraint::KEY,
                        'course_id'  => $requestPayload['course_id'],
                        'start_time' => $requestPayload['start_time'],
                        'end_time'   => $requestPayload['end_time'],
                        'day'        => $requestPayload['day'],
                    ],
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
            $this->enforce($state, $cRequestedWindow, $requestPayload, $context);
        }
    }
    private function enforce(State $state, $cRequestedSlot, $requestPayload, $context)
    {
        $day = $cRequestedSlot["day"];
        $startTime = $cRequestedSlot["start_time"];
        $endTime = $cRequestedSlot["end_time"];

        $found = false;

        $hall = app(GetHallByAvailabilityScore::class)->getHallByAvailabilityScore(
            $requestPayload,
            [
                'day' => $day,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ],
            $state
        )->first();

        $teacherId = $context->tCourses()->firstWhere("course_id", $cRequestedSlot["course_id"])['teacher_id'];

        foreach ($state->grid as $slot) {
            if (
                $slot->day === $day &&
                $slot->start_time === $startTime &&
                $slot->end_time === $endTime
            ) {
                $slot->hall_id = $hall['hall_id'] ?? null;
                $slot->teacher_id = $teacherId ?? null;
                $slot->course_id = $cRequestedSlot['course_id'];

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
            $gridSlotDto->hall_id = $hall['hall_id'] ?? null;
            $gridSlotDto->teacher_id = $teacherId ?? null;
            $gridSlotDto->course_id = $cRequestedSlot['course_id'];

            $state->grid[] = $gridSlotDto;
        }
    }
}
