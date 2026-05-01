<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization\Resolvers\Schedule;

use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Contracts\ResolverContract;
use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
use App\Constant\Violation\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodBlocker;
use App\Constant\Constraint\SemesterTimetable\Schedule\RequestedFreePeriod as RequestedFreePeriodConstraint;
use App\Schedular\SemesterTimetable\Suggestion\DTO\ScenarioDTO;
use App\Schedular\SemesterTimetable\Suggestion\Normalization\Helpers\Methods;
use Carbon\Carbon;

class RequestedFreePeriodResolver extends SuggestionContext implements ResolverContract
{
    private const NORMALIZABLE = [
        BreakPeriod::KEY,
        OperationalPeriod::KEY,
        PeriodDuration::KEY,
        RequiredJointCourse::KEY,
    ];

    public function supports(string $type): bool
    {
        return $type === RequestedFreePeriodBlocker::KEY ||  $type === RequestedFreePeriodConstraint::KEY;
    }

    public function normalize(ScenarioDTO $scenario)
    {
        $intent      = $scenario->decision->target_details;
        $day         = strtolower($intent['day']);
        $intentStart = Carbon::createFromFormat('H:i', $intent['start_time']);

        $scenario->decision->original_slot = [
            'start_time' => $intent['start_time'],
            'end_time'   => $intent['end_time'],
            'day'        => $day,
        ];
        $scenario->decision->preserved_slot = null;
        $scenario->decision->was_normalized = false;

        $blockerTypes = collect($scenario->resolutions)
            ->pluck('target_type')
            ->unique();

        $hasNormalizableBlocker = $blockerTypes->some(
            fn($type) => in_array($type, self::NORMALIZABLE, strict: true)
        );

        if (!$hasNormalizableBlocker) {
            return;
        }

        $candidates = app(Methods::class)->buildCandidatePool($day);

        if ($candidates->isEmpty()) {
            return;
        }

        $best = $candidates
            ->sortBy(
                fn($slot) =>
                Carbon::createFromFormat('H:i', $slot['start_time'])
                    ->diffInMinutes($intentStart, absolute: true)
            )
            ->first();

        if ($best !== null) {
            $scenario->decision->preserved_slot = $best;
            $scenario->decision->was_normalized = true;
        }
    }
}
