<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Normalization;

use App\Constant\Violation\SemesterTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\SemesterTimetable\Schedule\BreakPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\OperationalPeriod;
use App\Constant\Violation\SemesterTimetable\Schedule\PeriodDuration;
// use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScenarioNormalizationEngine extends SuggestionContext
{
    private const NORMALIZABLE = [
        BreakPeriod::KEY,
        OperationalPeriod::KEY,
        PeriodDuration::KEY,
        RequiredJointCourse::KEY,
    ];

    public function normalize(array &$scenarios): void
    {
        foreach ($scenarios as $scenario) {
            $intent    = $scenario->decision->target_details;
            $day       = strtolower($intent['day']);
            $intentStart = Carbon::createFromFormat('H:i', $intent['start_time']);
            // $intentEnd   = Carbon::createFromFormat('H:i', $intent['end_time']);


            $scenario->decision->original_slot = [
                'start_time' => $intent['start_time'],
                'end_time'   => $intent['end_time'],
                'day'        => $day,
            ];

            $scenario->decision->preserved_slot  = null;
            $scenario->decision->was_normalized  = false;

            $blockerTypes = collect($scenario->resolutions)
                ->pluck('target_type')
                ->unique()
                ->values();

            $hasNormalizableBlocker = $blockerTypes->some(
                fn($type) => in_array($type, self::NORMALIZABLE, strict: true)
            );

            if (!$hasNormalizableBlocker) {
                continue;
            }

            $candidates = $this->buildCandidatePool($day);

            if ($candidates->isEmpty()) {
                continue;
            }

            $best = $candidates->sortBy(function ($slot) use ($intentStart) {
                $candidateStart = Carbon::createFromFormat('H:i', $slot['start_time']);

                return $candidateStart->diffInMinutes($intentStart, absolute: true);
            })->first();


            if ($best) {
                $scenario->decision->preserved_slot = $best;
                $scenario->decision->was_normalized = true;
            }
        }
    }

    /**
     * Starts from all regular slots on the given day then progressively
     * strips out slots that would recreate each type of blocker present.
     */
    private function buildCandidatePool(string $day): Collection
    {
        $candidates = collect(self::$timetableGrid)
            ->filter(
                fn($slot) =>
                $slot->day  === $day &&
                    $slot->type === GridSlotDTO::TYPE_REGULAR
            )
            ->map(fn($slot) => [
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ]);


        return $candidates->values();
    }

    // protected function checkTeacherBusy($slot){
    //     $context = ConstraintContext::fromPayload(self::$requestPayload);
    //     $context->tBusySlotsForDay($slot["day"]);
    //     $context->teachers();
    // }

    // protected function checkTeacherAvailable($slot){
    //      $context = ConstraintContext::fromPayload(self::$requestPayload);
    //      $context->tPreferredSlotsForDay($slot["day"]);
    //      $context->teachers();
    // }
    // protected function checkHallAvailable($slot){
    //     $context = ConstraintContext::fromPayload(self::$requestPayload);
    //     $context->halls();
    //     $context->hBusySlotsFor($slot["day"]);
    // }
}
