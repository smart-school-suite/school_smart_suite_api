<?php

namespace App\Schedular\ExamTimetable\Placement\Engine;

use App\Constant\Violation\ExamTimetable\Student\StudentDailyLoad;
use App\Schedular\ExamTimetable\Context\RequestContext;
use App\Schedular\ExamTimetable\Core\State;
use App\Schedular\ExamTimetable\DTO\AllocationDTO;
use App\Schedular\ExamTimetable\Helpers\HallAvailability;
use App\Schedular\ExamTimetable\Helpers\InvigilatorAvailability;
use App\Schedular\ExamTimetable\Helpers\HallBuilder;
use App\Schedular\ExamTimetable\Placement\Scoring\ExamDateScoring;
use App\Schedular\ExamTimetable\Placement\Scoring\ExamSlotScore;
use Illuminate\Support\Facades\Log;

class PlacementEngine
{

    public function run(State $state)
    {
        $context = RequestContext::fromPayload();
        $hallService = app(HallAvailability::class);
        $invigService = app(InvigilatorAvailability::class);
        $examDates = collect(app(ExamDateScoring::class)->ScoreGridByBusyness($state))->sortBy('allocations')->values();
        $candidateCount = $context->candidateCount();
        $specialtyId = $context->specialty();
        $courses = $context->courses();
        $coursesPlaced = 0;
        $unplacedCourse = collect();
        foreach ($courses as $course) {
            if ($coursesPlaced === $courses->count()) {
                return;
            }

            $courseId = $course['course_id'];
            $sessionDuration = $context->sessionDurationCourse($courseId);
            $attemptedDates = collect();

            foreach ($examDates as $examDate) {
                $date = $examDate['date'];
                $dateAllocations = $examDate['allocations'];
                $allocation = (int) ($dateAllocations + 1);
                $studentLoad = $context->sDailyLoadRangeDate($date);

                if ($allocation > $studentLoad['max_sessions']) {
                    $attemptedDates->push($date);

                    if ($examDates->pluck('date')->diff($attemptedDates)->isNotEmpty()) {
                        $slot = collect(app(ExamSlotScore::class)->suggestSlots(
                            $state->dateGrid[$date],
                            $sessionDuration
                        ))->first();

                        $hall = $hallService->availableHalls(
                            $date,
                            $slot['start_time'],
                            $slot['end_time']
                        )->first();

                        $invigilator = collect($invigService->getAvailableInvigilators([
                            'date'       => $date,
                            'start_time' => $slot['start_time'],
                            'end_time'   => $slot['end_time'],
                        ]))->first();


                        $state->dateGrid[$date]->allocations[] = [
                            "type"       => AllocationDTO::TYPE_REGULAR,
                            "start_time" => $slot['start_time'],
                            "end_time"   => $slot['end_time'],
                            "hall"       => isset($hall['group'])
                                ? app(HallBuilder::class)->buildGroupedHall(
                                    $courseId,
                                    $hall,
                                    $invigilator,
                                    $candidateCount,
                                    $specialtyId
                                )
                                : app(HallBuilder::class)->buildSingleHall(
                                    $courseId,
                                    $hall,
                                    $invigilator,
                                    $candidateCount,
                                    $specialtyId
                                ),
                        ];

                        $existingKey = collect($state->violations['soft'])->search(function ($item) use ($date) {
                            return $item['constraint_failed']['key'] === StudentDailyLoad::KEY &&
                                $item['constraint_failed']['date'] === $date;
                        });

                        if ($existingKey !== false) {
                            $state->violations['soft'][$existingKey]['constraint_failed']['count']++;
                        } else {
                            $state->violations['soft'][] = [
                                'constraint_failed' => [
                                    'key'          => StudentDailyLoad::KEY,
                                    'date'         => $date,
                                    'breach'       => 'max',
                                    'count'        => 1,
                                    'max_sessions' => $studentLoad['max_sessions'],
                                ],
                                'violations' => [],
                            ];
                        }

                        $examDates->transform(function ($examDate) use ($date) {
                            if ($examDate['date'] === $date) {
                                $examDate['allocations'] += 1;
                            }
                            return $examDate;
                        });

                        $examDates = $examDates->sortBy('allocations')->values();

                        $coursesPlaced++;

                    } else {
                        $unplacedCourse->push($courseId);
                    }

                    continue;
                }

                $slot = collect(app(ExamSlotScore::class)->suggestSlots(
                    $state->dateGrid[$date],
                    $sessionDuration
                ))->first();


                $hall = $hallService->availableHalls(
                    $date,
                    $slot['start_time'],
                    $slot['end_time']
                )->first();


                $invigilator = collect($invigService->getAvailableInvigilators([
                    'date'       => $date,
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                ]))->first();


                $state->dateGrid[$date]->allocations[] = [
                    "type"       => AllocationDTO::TYPE_REGULAR,
                    "start_time" => $slot['start_time'],
                    "end_time"   => $slot['end_time'],
                    "hall"       => isset($hall['group'])
                        ? app(HallBuilder::class)->buildGroupedHall(
                            $courseId,
                            $hall,
                            $invigilator,
                            $candidateCount,
                            $specialtyId
                        )
                        : app(HallBuilder::class)->buildSingleHall(
                            $courseId,
                            $hall,
                            $invigilator,
                            $candidateCount,
                            $specialtyId
                        ),
                ];

                $examDates->transform(function ($examDate) use ($date) {
                    if ($examDate['date'] === $date) {
                        $examDate['allocations'] += 1;
                    }
                    return $examDate;
                });

                $examDates = $examDates->sortBy('allocations')->values();

                $coursesPlaced++;

                break;
            }
        }
    }
}
