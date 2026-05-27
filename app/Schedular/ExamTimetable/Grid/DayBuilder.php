<?php

namespace App\Schedular\ExamTimetable\Grid;

use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\DTO\AllocationDTO;
use App\Schedular\ExamTimetable\Exceptions\HardConstraintFailureException;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\HallBuilder;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;

class DayBuilder
{
    public function build(State $state)
    {

        $context = RequestContext::fromPayload();
        if (collect($this->validateJointCourses($context))->isNotEmpty()) {
            $state->violations["hard"] = [
                ...$this->validateJointCourses($context)
            ];
            throw new HardConstraintFailureException();
        }
        $examDuration = $context->examDuration();
        $state->dateGrid = $this->generateDateGrid($context, $examDuration);
        $this->buildJointCourse($state);
    }

    protected function buildJointCourse(State $state)
    {
        $context = RequestContext::fromPayload();
        $jointCourseReqs = $context->jointCourses();
        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();

        $hallService = app(HallAvailability::class);
        $invigService = app(InvigilatorAvailability::class);

        foreach ($jointCourseReqs as $jointCourse) {
            $date = $jointCourse['date'];

            $availableInvigs = collect($invigService->getAvailableInvigilators([
                'date' => $date,
                'start_time' => $jointCourse['start_time'],
                'end_time' => $jointCourse['end_time']
            ]))->first();

            $hall = $hallService->availableHalls(
                $date,
                $jointCourse['start_time'],
                $jointCourse['end_time']
            )->first();

            $state->dateGrid[$date]->allocations[] = [
                "type" => AllocationDTO::TYPE_JOINT,
                "start_time" => $jointCourse['start_time'],
                "end_time" => $jointCourse['end_time'],
                "hall" => isset($hall['group']) ?  app(HallBuilder::class)->buildGroupedHall(
                    $jointCourse["course_id"],
                    $hall,
                    $availableInvigs,
                    $candidateCount,
                    $specialtyId
                ) : app(HallBuilder::class)->buildSingleHall(
                    $jointCourse["course_id"],
                    $hall,
                    $availableInvigs,
                    $candidateCount,
                    $specialtyId
                ),
            ];
        }

        return null;
    }
    protected function generateDateGrid(RequestContext $context, array $examDuration): array
    {
        $grid = [];
        $operationalDates = $context->operationalDates();

        $startDate = $examDuration['start_date'];
        $endDate = $examDuration['end_date'];

        foreach ($operationalDates as $dateString) {
            if ($dateString >= $startDate && $dateString <= $endDate) {
                $operationalHours = $context->operationalHourDate($dateString);

                $startTime = $operationalHours['start_time'] ?? '08:00';
                $endTime = $operationalHours['end_time'] ?? '17:00';

                $grid[$dateString] = new ExamDateDTO(
                    date: $dateString,
                    startTime: $startTime,
                    endTime: $endTime,
                    allocations: []
                );
            }
        }

        return $grid;
    }
    protected function validateJointCourses(RequestContext $context)
    {
        $reasons = [];
        $hallService = app(HallAvailability::class);
        $invigService = app(InvigilatorAvailability::class);

        foreach ($context->jointCourses() as $jointCourse) {
            $jcDate = $jointCourse["date"];
            $jcStart = $jointCourse["start_time"];
            $jcEnd = $jointCourse["end_time"];
            $jcCourseId = $jointCourse["course_id"];

            $violations = [];

            // Fix #1: Check if date is NOT operational
            if (!$context->isOperationalDate($jcDate)) {
                $violations[] = [
                    "key" => OperationalPeriod::KEY,
                    "type" => "invalid_operational_date",
                    "allowed_dates" => $context->operationalDates()
                ];
            }

            $opWin = $context->operationalHourDate($jcDate);

            if ($opWin === null) {
                $violations[] = [
                    "key" => OperationalPeriod::KEY,
                    "type" => "date_excluded_from_operations",
                    "date" => $jcDate
                ];
            } else {
                // Fix #3: Only check hours if opWin exists
                if ($jcStart < $opWin['start_time'] || $jcEnd > $opWin['end_time']) {
                    $violations[] = [
                        "key" => OperationalPeriod::KEY,
                        "type" => "invalid_operational_hour",
                        "allowed_time" => [
                            "start_time" => $opWin["start_time"],
                            "end_time" => $opWin["end_time"],
                            "date" => $jcDate
                        ]
                    ];
                }
            }

            $availableInvigs = collect($invigService->getAvailableInvigilators([
                'date' => $jcDate,
                'start_time' => $jcStart,
                'end_time' => $jcEnd
            ]));

            if ($availableInvigs->isEmpty()) {
                $violations[] = [
                    "key" => "invigilator_unavailable",
                    "resource" => "invigilator",
                    "available_invigs" => $availableInvigs->count()
                ];
            }

            $halls = $hallService->availableHalls(
                $jcDate,
                $jcStart,
                $jcEnd
            );

            if ($halls->isEmpty()) {
                $violations[] = [
                    "key" => "hall_unavailable",
                    "resource" => "hall",
                    "available_halls" => $halls->count()
                ];
            }

            if (!empty($violations)) {
                $reasons[] = [
                    'constraint_failed' => [
                        'key'        => RequiredJointCourseSession::KEY,
                        'date'       => $jcDate,
                        'course_id'  => $jcCourseId ?? null,
                        'start_time' => $jcStart,
                        'end_time'   => $jcEnd,
                    ],
                    "violations" => $violations
                ];
            }
        }

        return $reasons;
    }
}
