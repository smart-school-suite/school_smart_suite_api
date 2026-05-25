<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Contracts\ResolverContract;
use Carbon\Carbon;

class BreakPeriodResolver extends SuggestionContext implements ResolverContract
{
    public function supports(string $type): bool
    {
        return $type === BreakPeriodBlocker::KEY || $type  === BreakPeriodConstraint::KEY;
    }
    public function normalize(ScenarioDTO $scenario)
    {
        $context = ConstraintContext::fromPayload(self::$requestPayload);
        $intent = $scenario->decision->target_details;
        $day = strtolower($intent['day']);
        $intentStart = Carbon::parse($intent['start_time']);
        $intentEnd = Carbon::parse($intent['end_time']);

        $jointCourses = $context->jointCourses($day)->toArray();
        $operationalPeriod = $context->operationalWindow($day);

        // Store original slot
        $scenario->decision->original_slot = [
            'start_time' => $intent['start_time'],
            'end_time'   => $intent['end_time'],
            'day'        => $day,
        ];

        // Check if normalization is needed
        $needsNormalization = $this->needsNormalization(
            $intentStart,
            $intentEnd,
            $operationalPeriod,
            $jointCourses
        );

        if (!$needsNormalization) {
            $scenario->decision->preserved_slot = null;
            $scenario->decision->was_normalized = false;
            return;
        }

        // Find the closest available slot preserving duration
        $duration = $intentStart->diffInMinutes($intentEnd);
        $normalizedSlot = $this->findClosestAvailableSlot(
            $intentStart,
            $duration,
            $operationalPeriod,
            $jointCourses
        );

        $scenario->decision->preserved_slot = $normalizedSlot;
        $scenario->decision->was_normalized = true;
    }

    private function needsNormalization(
        Carbon $intentStart,
        Carbon $intentEnd,
        array $operationalPeriod,
        array $jointCourses
    ): bool {
        // Check if within operational period
        $opStart = Carbon::parse($operationalPeriod['start']);
        $opEnd = Carbon::parse($operationalPeriod['end']);

        if ($intentStart->lt($opStart) || $intentEnd->gt($opEnd)) {
            return true;
        }

        // Check for conflicts with joint courses
        foreach ($jointCourses as $jointCourse) {
            $courseStart = Carbon::createFromFormat('H:i', $jointCourse['start_time']);
            $courseEnd = Carbon::createFromFormat('H:i', $jointCourse['end_time']);

            if ($this->timeRangesOverlap($intentStart, $intentEnd, $courseStart, $courseEnd)) {
                return true;
            }
        }

        return false;
    }

    private function findClosestAvailableSlot(
        Carbon $originalStart,
        int $duration,
        array $operationalPeriod,
        array $jointCourses
    ): array {
        $opStart = Carbon::parse($operationalPeriod['start']);
        $opEnd = Carbon::parse($operationalPeriod['end']);

        // Generate all possible start times within operational period
        $possibleStartTimes = $this->generatePossibleStartTimes(
            $opStart,
            $opEnd,
            $jointCourses,
            $duration
        );

        // Find the start time closest to the original start time
        $bestStart = null;
        $minDistance = PHP_INT_MAX;

        foreach ($possibleStartTimes as $startTime) {
            $distance = abs($startTime->diffInMinutes($originalStart));
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $bestStart = $startTime;
            }
        }

        // If no available slot found, return original (shouldn't happen if operational period has enough time)
        if ($bestStart === null) {
            return [
                'start_time' => $originalStart->format('H:i'),
                'end_time' => $originalStart->copy()->addMinutes($duration)->format('H:i'),
                'day' => strtolower($originalStart->format('l'))
            ];
        }

        return [
            'start_time' => $bestStart->format('H:i'),
            'end_time' => $bestStart->copy()->addMinutes($duration)->format('H:i'),
            'day' => strtolower($originalStart->format('l'))
        ];
    }


    private function isSlotAvailable(
        Carbon $startTime,
        int $duration,
        array $jointCourses,
        Carbon $opEnd
    ): bool {
        $endTime = $startTime->copy()->addMinutes($duration);

        // Check operational period boundaries
        if ($endTime->gt($opEnd)) {
            return false;
        }

        // Check conflicts with joint courses
        foreach ($jointCourses as $course) {
            $courseStart = Carbon::createFromFormat('H:i', $course['start_time']);
            $courseEnd = Carbon::createFromFormat('H:i', $course['end_time']);

            if ($this->timeRangesOverlap($startTime, $endTime, $courseStart, $courseEnd)) {
                return false;
            }
        }

        return true;
    }

    private function timeRangesOverlap(
        Carbon $start1,
        Carbon $end1,
        Carbon $start2,
        Carbon $end2
    ): bool {
        return $start1->lt($end2) && $end1->gt($start2);
    }
    private function generatePossibleStartTimes(
        Carbon $opStart,
        Carbon $opEnd,
        array $jointCourses,
        int $duration
    ): array {
        $possibleStarts = [];

        // Convert operational period to minutes for iteration
        $opStartMinutes = $opStart->hour * 60 + $opStart->minute;
        $opEndMinutes = $opEnd->hour * 60 + $opEnd->minute;

        // Generate every possible start time at 15-minute intervals
        // (You can adjust the interval granularity as needed)
        $interval = 15; // minutes

        for ($minutes = $opStartMinutes; $minutes <= $opEndMinutes - $duration; $minutes += $interval) {
            $potentialStart = Carbon::createFromTime(
                floor($minutes / 60),
                $minutes % 60,
                0
            );

            if ($this->isSlotAvailable($potentialStart, $duration, $jointCourses, $opEnd)) {
                $possibleStarts[] = $potentialStart;
            }
        }

        // Also add boundaries from joint courses for precision
        foreach ($jointCourses as $course) {
            $courseStart = Carbon::createFromFormat('H:i', $course['start_time']);
            $courseEnd = Carbon::createFromFormat('H:i', $course['end_time']);

            // Add times right after courses end
            $afterCourseEnd = $courseEnd->copy();
            if (
                $afterCourseEnd->gte($opStart) &&
                $this->isSlotAvailable($afterCourseEnd, $duration, $jointCourses, $opEnd)
            ) {
                $possibleStarts[] = $afterCourseEnd;
            }

            // Add times that would end right when course starts
            $beforeCourseStart = $courseStart->copy()->subMinutes($duration);
            if (
                $beforeCourseStart->gte($opStart) &&
                $this->isSlotAvailable($beforeCourseStart, $duration, $jointCourses, $opEnd)
            ) {
                $possibleStarts[] = $beforeCourseStart;
            }
        }

        // Remove duplicates and sort
        $uniqueStarts = [];
        foreach ($possibleStarts as $start) {
            $key = $start->format('H:i');
            if (!isset($uniqueStarts[$key])) {
                $uniqueStarts[$key] = $start;
            }
        }

        $sortedStarts = array_values($uniqueStarts);
        usort($sortedStarts, function ($a, $b) {
            return $a->timestamp - $b->timestamp;
        });

        return $sortedStarts;
    }
}
