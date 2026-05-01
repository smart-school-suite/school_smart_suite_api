<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod as BreakPeriodBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ResolutionDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;

class BreakPeriodRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === BreakPeriodBlocker::KEY || $type === BreakPeriodConstraint::KEY;
    }

    public function resolve(ResolutionDTO $resolution, array $params): array
    {
        return self::isHardScenario()
            ? $this->resolveHard($resolution, $params)
            : $this->resolveSoft($resolution, $params);
    }

    protected function resolveSoft(ResolutionDTO $resolution, array $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);
        return  [
            "day" => $pDay,
            "start_time" => $pStart,
            "end_time" => $pEnd
        ];
    }

    protected function resolveHard(ResolutionDTO $resolution, array $params): array
    {
        // Preserved slot (acts as the operational period/boundary)
        $pSlot = $params['preserve_slot'];
        $pStart = Carbon::parse($pSlot['start_time']);
        $pEnd = Carbon::parse($pSlot['end_time']);
        $pDay = strtolower($pSlot['day']);

        // User intent break that failed constraint
        $intent = $resolution->meta["constraint_failed"];
        $intentStart = Carbon::parse($intent["start_time"]);
        $intentEnd = Carbon::parse($intent["end_time"]);
        $duration = $intentStart->diffInMinutes($intentEnd);

        // Get joint courses for the preserved slot day
        $context = ConstraintContext::fromPayload(self::$requestPayload);
        $jointCourses = $context->jointCourses($pDay)->toArray();

        // Filter joint courses to only those that fall within the preserved slot boundaries
        $relevantJointCourses = $this->filterJointCoursesWithinBoundary(
            $jointCourses,
            $pStart,
            $pEnd
        );

        // Check if normalization is needed
        $needsNormalization = $this->needsNormalizationForResolveHard(
            $intentStart,
            $intentEnd,
            $pStart,
            $pEnd,
            $relevantJointCourses
        );

        if (!$needsNormalization) {
            // No normalization needed, return the original intent
            return [
                'start_time' => $intent["start_time"],
                'end_time' => $intent["end_time"],
                'day' => $intent["day"],
                'was_normalized' => false
            ];
        }

        // Find the closest available slot within the preserved slot boundaries
        $normalizedSlot = $this->findClosestAvailableSlotWithinBoundary(
            $intentStart,
            $duration,
            $pStart,
            $pEnd,
            $relevantJointCourses
        );

        return [
            'start_time' => $normalizedSlot['start_time'],
            'end_time' => $normalizedSlot['end_time'],
            'day' => $pDay
        ];
    }

    /**
     * Filter joint courses to only those that fall within the preserved slot boundaries
     */
    private function filterJointCoursesWithinBoundary(
        array $jointCourses,
        Carbon $boundaryStart,
        Carbon $boundaryEnd
    ): array {
        return array_filter($jointCourses, function ($course) use ($boundaryStart, $boundaryEnd) {
            $courseStart = Carbon::createFromFormat('H:i', $course['start_time']);
            $courseEnd = Carbon::createFromFormat('H:i', $course['end_time']);

            // Check if course overlaps with the preserved slot boundary
            return $this->timeRangesOverlap($boundaryStart, $boundaryEnd, $courseStart, $courseEnd);
        });
    }

    /**
     * Check if normalization is needed for resolve hard
     */
    private function needsNormalizationForResolveHard(
        Carbon $intentStart,
        Carbon $intentEnd,
        Carbon $boundaryStart,
        Carbon $boundaryEnd,
        array $jointCourses
    ): bool {
        if ($intentStart->lt($boundaryStart) || $intentEnd->gt($boundaryEnd)) {
            return true;
        }

        foreach ($jointCourses as $jointCourse) {
            $courseStart = Carbon::createFromFormat('H:i', $jointCourse['start_time']);
            $courseEnd = Carbon::createFromFormat('H:i', $jointCourse['end_time']);

            if ($this->timeRangesOverlap($intentStart, $intentEnd, $courseStart, $courseEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find closest available slot within the preserved slot boundary
     */
    private function findClosestAvailableSlotWithinBoundary(
        Carbon $originalStart,
        int $duration,
        Carbon $boundaryStart,
        Carbon $boundaryEnd,
        array $jointCourses
    ): array {
        // Generate all possible start times within boundary
        $possibleStartTimes = $this->generatePossibleStartTimesWithinBoundary(
            $boundaryStart,
            $boundaryEnd,
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

        // If no available slot found, return a fallback (end of boundary minus duration)
        if ($bestStart === null) {
            $fallbackStart = $boundaryEnd->copy()->subMinutes($duration);
            return [
                'start_time' => $fallbackStart->format('H:i'),
                'end_time' => $boundaryEnd->format('H:i'),
                'day' => strtolower($boundaryStart->format('l'))
            ];
        }

        return [
            'start_time' => $bestStart->format('H:i'),
            'end_time' => $bestStart->copy()->addMinutes($duration)->format('H:i'),
            'day' => strtolower($boundaryStart->format('l'))
        ];
    }

    /**
     * Generate possible start times within the boundary
     */
    private function generatePossibleStartTimesWithinBoundary(
        Carbon $boundaryStart,
        Carbon $boundaryEnd,
        array $jointCourses,
        int $duration
    ): array {
        $possibleStarts = [];

        // Convert boundary to minutes for iteration
        $boundaryStartMinutes = $boundaryStart->hour * 60 + $boundaryStart->minute;
        $boundaryEndMinutes = $boundaryEnd->hour * 60 + $boundaryEnd->minute;

        // Generate every possible start time at 15-minute intervals
        $interval = 15; // minutes

        for ($minutes = $boundaryStartMinutes; $minutes <= $boundaryEndMinutes - $duration; $minutes += $interval) {
            $potentialStart = Carbon::createFromTime(
                floor($minutes / 60),
                $minutes % 60,
                0
            );

            if ($this->isSlotAvailableWithinBoundary($potentialStart, $duration, $jointCourses, $boundaryEnd)) {
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
                $afterCourseEnd->gte($boundaryStart) &&
                $this->isSlotAvailableWithinBoundary($afterCourseEnd, $duration, $jointCourses, $boundaryEnd)
            ) {
                $possibleStarts[] = $afterCourseEnd;
            }

            // Add times that would end right when course starts
            $beforeCourseStart = $courseStart->copy()->subMinutes($duration);
            if (
                $beforeCourseStart->gte($boundaryStart) &&
                $this->isSlotAvailableWithinBoundary($beforeCourseStart, $duration, $jointCourses, $boundaryEnd)
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

    /**
     * Check if a slot is available within the boundary
     */
    private function isSlotAvailableWithinBoundary(
        Carbon $startTime,
        int $duration,
        array $jointCourses,
        Carbon $boundaryEnd
    ): bool {
        $endTime = $startTime->copy()->addMinutes($duration);

        // Check boundary boundaries
        if ($endTime->gt($boundaryEnd)) {
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
}
