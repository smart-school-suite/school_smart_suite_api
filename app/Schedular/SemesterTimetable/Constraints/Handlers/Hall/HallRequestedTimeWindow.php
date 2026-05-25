<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Hall;

use App\Constant\Constraint\SemesterTimetable\Hall\HallRequestedTimeWindow as HrequestedWindowConstraint;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\CourseRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\JointCoursePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\BreakPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\PeriodDurationValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\RequestedFreePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Helpers\GetTeacherByWorkLoadScore;
use Illuminate\Support\Arr;

class HallRequestedTimeWindow implements ConstraintHandler
{
    public static function supports(): string
    {
        return HrequestedWindowConstraint::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        foreach ($context->hRequestedWindows() as $hrequestedWindow) {
            $params = [
                'hall_id'  => $hrequestedWindow['hall_id'],
                'start_time' => $hrequestedWindow['start_time'],
                'end_time'   => $hrequestedWindow['end_time'],
                'day'        => $hrequestedWindow['day'],
                'slot_type'  => HrequestedWindowConstraint::KEY,
            ];

            $blockers = array_filter([
                //app(TeacherUnavailableValidator::class)->check($context, $params),
                app(BreakPeriodValidator::class)->check($context, $params),
                app(OperationalPeriodValidator::class)->check($context, $params),
                app(PeriodDurationValidator::class)->check($context, $params),
                ...app(TeacherRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(HallBusyValidator::class)->check($context, $params),
                ...app(TeacherBusyValidator::class)->check($context, $params),
                ...app(JointCoursePeriodValidator::class)->check($context, $params),
                ...app(RequestedFreePeriodValidator::class)->check($context, $params),
                ...app(RequestedAssignmentValidator::class)->check($context, $params),
                ...app(CourseRequestedTimeSlotValidator::class)->check($context, $params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key'        => HrequestedWindowConstraint::KEY,
                        'hall_id'  => $hrequestedWindow['hall_id'],
                        'start_time' => $hrequestedWindow['start_time'],
                        'end_time'   => $hrequestedWindow['end_time'],
                        'day'        => $hrequestedWindow['day'],
                    ],
                    'blockers' => array_values($blockers),
                ];
                continue;
            }
            $this->enforce($state, $hrequestedWindow, $requestPayload, $context);
        }
    }

    private function enforce(State $state, $hrequestedWindow, $requestPayload, $context): void
    {
        $day = $hrequestedWindow["day"];
        $startTime = $hrequestedWindow["start_time"];
        $endTime = $hrequestedWindow["end_time"];

        $found = false;
        $teacher = app(GetTeacherByWorkLoadScore::class)->getTeacherByAvailabilityScore(
            $requestPayload,
            [
                'day' => $day,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ],
            $state
        )->first();
        $courseId = $context->coursesForTeacher($teacher['teacher_id'])->pluck('course_id')->toArray();

        foreach ($state->grid as $slot) {
            if (
                $slot->day === $day &&
                $slot->start_time === $startTime &&
                $slot->end_time === $endTime
            ) {
                $slot->type = GridSlotDTO::TYPE_REGULAR;
                $slot->hall_id = $hrequestedWindow["hall_id"];
                $slot->teacher_id = $teacher['teacher_id'] ?? null;
                $slot->course_id = Arr::random($courseId) ?? null;
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
            $gridSlotDto->hall_id = $hrequestedWindow["hall_id"];
            $gridSlotDto->teacher_id = $teacher['teacher_id'] ?? null;
            $gridSlotDto->course_id = Arr::random($courseId) ?? null;;

            $state->grid[] = $gridSlotDto;
        }
    }
}
