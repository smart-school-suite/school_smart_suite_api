<?php

namespace App\Schedular\ExamTimetable\Grid;

use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\ExamDateDTO;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use DateTime;
use DateInterval;
use DatePeriod;

class DayBuilder
{
    public function build(State $state)
    {
        $context = RequestContext::fromPayload();
        $examDuration = $context->examDuration();

        $state->dateGrid = $this->generateDateGrid($context, $examDuration);
        $this->buildJointCourse($state);
    }

    protected function buildJointCourse(State $state)
    {
        $result = [];
        $context = RequestContext::fromPayload();
        $jointCourseReqs = $context->jointCourses();
        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();

        $hallService = app(HallAvailability::class);
        $invigService = app(InvigilatorAvailability::class);

        foreach ($jointCourseReqs as $jointCourse) {
            $date = $jointCourse['date'];

            $availableInvigs = $invigService->getAvailableInvigilators([
                'date' => $date,
                'start_time' => $jointCourse['start_time'],
                'end_time' => $jointCourse['end_time']
            ]);

            $hall = collect($hallService->availableHalls(
                $date,
                $jointCourse['start_time'],
                $jointCourse['end_time']
            ));

            $state->dateGrid[$date]->allocations[] = [
                "start_time" => $jointCourse['start_time'],
                "end_time" => $jointCourse['end_time'],
                "hall" => $hall,

            ];

            $result[] = [
                "jointCourse" => $jointCourse,
                "availableHalls" => $hall,
                "availableInvigilators" => $availableInvigs,
            ];
        }

        return $result;
    }
    protected function generateDateGrid(RequestContext $context, array $examDuration): array
    {
        $grid = [];

        $startDate = new DateTime($examDuration['start_date']);
        $endDate = new DateTime($examDuration['end_date']);
        $endDate->modify('+1 day');

        $interval = new DateInterval('P1D');
        $period = new DatePeriod($startDate, $interval, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');

            $operationalHours = $context->operationalHourDate($dateString);

            $startTime = $operationalHours['startTime'] ?? '08:00';
            $endTime = $operationalHours['endTime'] ?? '17:00';

            $grid[$dateString] = new ExamDateDTO(
                date: $dateString,
                startTime: $startTime,
                endTime: $endTime,
                allocations: []
            );
        }

        return $grid;
    }
}
