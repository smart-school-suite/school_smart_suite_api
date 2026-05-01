<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule;

use App\Constant\Constraint\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationConstraint;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration as PeriodDurationBlocker;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Contracts\ResolverContract;
use Carbon\Carbon;

class PeriodDurationResolver extends SuggestionContext implements ResolverContract
{
    protected array $allowedDurations = [
        30,
        60,
        45,
        120
    ];

    public function supports(string $type): bool
    {
        return $type === PeriodDurationBlocker::KEY || $type === PeriodDurationConstraint::KEY;
    }

    public function normalize(ScenarioDTO $scenario)
    {
        $context = ConstraintContext::fromPayload(self::$requestPayload);
        $intent = $scenario->decision->target_details;
        $day = strtolower($intent['day']);
        $intendedDuration = $intent["duration_minutes"];

        $operationalPeriod = $context->operationalWindow($day);
        $opStart = Carbon::createFromFormat('H:i', $operationalPeriod['start']);
        $opEnd = Carbon::createFromFormat('H:i', $operationalPeriod['end']);
        $availableMinutes = $opStart->diffInMinutes($opEnd);

        // Store original duration
        $scenario->decision->original_slot = [
            "day" => $day,
            "duration_minutes" => $intendedDuration
        ];

        // Check if normalization is needed
        if ($intendedDuration <= $availableMinutes) {
            // Duration fits within operational period
            $scenario->decision->preserved_slot = [];
            $scenario->decision->was_normalized = false;
            return;
        }

        // Find the best allowed duration that fits
        $normalizedDuration = $this->findClosestAllowedDuration($intendedDuration, $availableMinutes);

        $scenario->decision->preserved_slot = [
            "duration_minutes" => $normalizedDuration,
            "day" => $day
        ];
        $scenario->decision->was_normalized = true;
    }

    private function findClosestAllowedDuration(int $intendedDuration, int $availableMinutes): int
    {
        // Filter allowed durations that fit within operational period
        $validDurations = array_filter($this->allowedDurations, function ($duration) use ($availableMinutes) {
            return $duration <= $availableMinutes;
        });

        if (empty($validDurations)) {
            // If no duration fits, return the smallest allowed duration
            // (though this shouldn't happen if operational period has at least some time)
            return min($this->allowedDurations);
        }

        // Sort valid durations
        sort($validDurations);

        // Find the duration closest to intended duration
        $closestDuration = null;
        $minDifference = PHP_INT_MAX;

        foreach ($validDurations as $duration) {
            $difference = abs($duration - $intendedDuration);

            if ($difference < $minDifference) {
                $minDifference = $difference;
                $closestDuration = $duration;
            }
        }

        return $closestDuration;
    }
}
