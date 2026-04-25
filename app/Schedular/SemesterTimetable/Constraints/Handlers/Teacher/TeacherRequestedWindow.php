<?php

namespace App\Schedular\SemesterTimetable\Constraints\Handlers\Teacher;

use App\Constant\Constraint\SemesterTimetable\Teacher\TeacherRequestedTimeSlot;
use App\Schedular\SemesterTimetable\Constraints\Contracts\ConstraintHandler;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\Constraints\Validator\Assignment\RequestedAssignmentValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\CourseRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Course\JointCoursePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Hall\HallRequestedTimeSlotValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\BreakPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\OperationalPeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\PeriodDurationValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Schedule\RequestedFreePeriodValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherBusyValidator;
use App\Schedular\SemesterTimetable\Constraints\Validator\Teacher\TeacherUnavailableValidator;
use App\Schedular\SemesterTimetable\DTO\TimetableContext;
use App\Schedular\SemesterTimetable\Helpers\GetHallByAvailabilityScore;
use Illuminate\Support\Arr;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;

class TeacherRequestedWindow extends TimetableContext implements ConstraintHandler
{
    public static function supports(): string
    {
        return TeacherRequestedTimeSlot::KEY;
    }

    public function handle(array $requestPayload, State $state): void
    {
        $context = ConstraintContext::fromPayload($requestPayload);
        foreach ($context->tRequestedWindows() as $tRequestedWindow) {
            $params = [
                'teacher_id' => $tRequestedWindow['teacher_id'],
                'start_time' => $tRequestedWindow['start_time'],
                'end_time'   => $tRequestedWindow['end_time'],
                'day'        => $tRequestedWindow['day'],
                'slot_type'  => TeacherRequestedTimeSlot::KEY,
            ];

            $blockers = array_filter([
                self::isWithPreference() ? app(TeacherUnavailableValidator::class)->check($context, $params) : [],
                app(BreakPeriodValidator::class)->check($context, $params),
                app(OperationalPeriodValidator::class)->check($context, $params),
                app(PeriodDurationValidator::class)->check($context, $params),
                ...app(HallBusyValidator::class)->check($context, $params),
                ...app(TeacherBusyValidator::class)->check($context, $params),
                ...app(JointCoursePeriodValidator::class)->check($context, $params),
                ...app(RequestedFreePeriodValidator::class)->check($context, $params),
                ...app(RequestedAssignmentValidator::class)->check($context, $params),
                ...app(HallRequestedTimeSlotValidator::class)->check($context, $params),
                ...app(CourseRequestedTimeSlotValidator::class)->check($context, $params)
            ]);

            if (!empty($blockers)) {
                $state->violations['soft'][] = [
                    'constraint_failed' => [
                        'key'        => TeacherRequestedTimeSlot::KEY,
                        'teacher_id' => $tRequestedWindow['teacher_id'],
                        'start_time' => $tRequestedWindow['start_time'],
                        'end_time'   => $tRequestedWindow['end_time'],
                        'day'        => $tRequestedWindow['day'],
                    ],
                    'blockers' => $blockers
                ];
                continue;
            }
            $this->enforce($state, $tRequestedWindow, $requestPayload, $context);
        }
    }

    private function enforce(State $state, $trequestedWindow, $requestPayload, $context): void
    {
        $day = $trequestedWindow["day"];
        $startTime = $trequestedWindow["start_time"];
        $endTime = $trequestedWindow["end_time"];
        $teacherId = $trequestedWindow["teacher_id"];
        $found = false;
        $courseId = Arr::random($context->coursesForTeacher($teacherId)->toArray());
        $hall = app(GetHallByAvailabilityScore::class)->getHallByAvailabilityScore(
            $requestPayload,
            [
                'day' => $day,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ],
            $state
        )->first();

        foreach ($state->grid as $slot) {
            if (
                $slot->day === $day &&
                $slot->start_time === $startTime &&
                $slot->end_time === $endTime
            ) {
                $slot->type = GridSlotDTO::TYPE_REGULAR;
                $slot->hall_id = $hall["hall_id"];
                $slot->teacher_id = $teacherId ?? null;
                $slot->course_id = $courseId  ?? null;
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
            $gridSlotDto->hall_id = $hall["hall_id"];
            $gridSlotDto->teacher_id = $teacherId ?? null;
            $gridSlotDto->course_id = $courseId  ?? null;;

            $state->grid[] = $gridSlotDto;
        }
    }
}
