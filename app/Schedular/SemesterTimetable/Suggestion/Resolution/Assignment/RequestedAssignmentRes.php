<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Resolution\Assignment;

use App\Constant\Constraint\SemesterTimetable\Assignment\RequestedAssignment as RequestedAssignmentConstraint;
use App\Constant\Violation\SemesterTimetable\Assignment\RequestedAssigment as RequestedAssignmentBlocker;
use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use App\Schedular\SemesterTimetable\Suggestion\Resolution\Contract\ResolutionContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RequestedAssignmentRes extends SuggestionContext implements ResolutionContract
{
    public function supports(string $type): bool
    {
        return $type === RequestedAssignmentConstraint::KEY || $type === RequestedAssignmentBlocker::KEY;
    }

    public function resolve($resolution, $params): array
    {
        $pSlot  = $params['preserve_slot'];
        $pStart = $pSlot['start_time'];
        $pEnd   = $pSlot['end_time'];
        $pDay   = strtolower($pSlot['day']);

        $intentDetails = $resolution->meta['blocker']->details;
        $iDay          = strtolower($intentDetails['day']);
        $iStartTime    = $intentDetails['start_time'];

        $intentStart = Carbon::createFromFormat('H:i', $iStartTime);

        $context         = ConstraintContext::fromPayload(self::$requestPayload);
        $isWithPreference = self::isWithPreference();

        $tBusy        = $context->tBusySlotsForDay($pDay);
        $tPreferred   = $context->tBusySlotsForDay($pDay);
        $teachers     = $context->teachers();
        $hallBusy     = $context->hBusySlotsFor($pDay);
        $halls        = $context->halls();

        // ── Build candidate slots ─────────────────────────────────────────
        // All regular slots on the preserve day excluding the preserved slot
        $candidates = collect(self::getTimetableGrid())
            ->filter(fn($slot) =>
                $slot->type  === GridSlotDTO::TYPE_REGULAR &&
                $slot->day   === $pDay &&
                !($slot->start_time === $pStart && $slot->end_time === $pEnd)
            )
            ->map(fn($slot) => [
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ]);

        // ── Enrich each candidate with available teachers and halls ───────
        $enriched = $candidates
            ->map(function ($slot) use (
                $teachers, $tBusy, $tPreferred,
                $halls, $hallBusy, $isWithPreference
            ) {
                $start = Carbon::createFromFormat('H:i', $slot['start_time']);
                $end   = Carbon::createFromFormat('H:i', $slot['end_time']);

                $availableTeachers = $this->availableTeachers(
                    $teachers, $tBusy, $tPreferred,
                    $start, $end, $isWithPreference
                );

                $availableHalls = $this->availableHalls(
                    $halls, $hallBusy, $start, $end
                );

                // slot only qualifies if it has at least one teacher and one hall
                if ($availableTeachers->isEmpty() || $availableHalls->isEmpty()) {
                    return null;
                }

                return [
                    ...$slot,
                    'available_teachers' => $availableTeachers->values()->all(),
                    'available_halls'    => $availableHalls->values()->all(),
                ];
            })
            ->filter() // remove null (unqualified) slots
            ->values();

        // ── Rank by proximity to user intent start time ───────────────────
        $ranked = $enriched->sortBy(fn($slot) =>
            Carbon::createFromFormat('H:i', $slot['start_time'])
                ->diffInMinutes($intentStart, absolute: true)
        )->values();

        return [
            'key'         => RequestedAssignmentConstraint::KEY,
            'intent_day'  => $iDay,
            'intent_time' => $iStartTime,
            'suggestions' => $ranked->all(),
        ];
    }

    // ─── Available teachers ───────────────────────────────────────────────

    private function availableTeachers(
        Collection $teachers,
        Collection $tBusy,
        Collection $tPreferred,
        Carbon     $start,
        Carbon     $end,
        bool       $isWithPreference
    ): Collection {
        return $teachers
            ->filter(function ($teacher) use ($tBusy, $tPreferred, $start, $end, $isWithPreference) {
                $teacherId = $teacher['teacher_id'];

                // must not be busy at this slot
                $isBusy = $tBusy
                    ->filter(fn($b) => $b['teacher_id'] === $teacherId)
                    ->some(fn($b) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $b['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $b['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                // preference check only when required
                if (!$isWithPreference) {
                    return true;
                }

                $prefs = $tPreferred->filter(fn($p) => $p['teacher_id'] === $teacherId);

                if ($prefs->isEmpty()) {
                    return true; // no preference defined → always available
                }

                return $prefs->some(fn($pref) =>
                    $start->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['start_time'])) &&
                    $end->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['end_time']))
                );
            })
            ->map(function ($teacher) use ($tBusy) {
                $busyMinutes = $tBusy
                    ->filter(fn($b) => $b['teacher_id'] === $teacher['teacher_id'])
                    ->sum(fn($b) =>
                        Carbon::createFromFormat('H:i', $b['start_time'])
                            ->diffInMinutes(Carbon::createFromFormat('H:i', $b['end_time']))
                    );

                return [...$teacher, 'busy_minutes' => $busyMinutes];
            })
            ->sortBy('busy_minutes')
            ->values();
    }

    // ─── Available halls ──────────────────────────────────────────────────

    private function availableHalls(
        Collection $halls,
        Collection $hallBusy,
        Carbon     $start,
        Carbon     $end
    ): Collection {
        return $halls
            ->filter(function ($hall) use ($hallBusy, $start, $end) {
                $isBusy = $hallBusy
                    ->filter(fn($b) => $b['hall_id'] === $hall['hall_id'])
                    ->some(fn($b) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $b['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $b['start_time']))
                    );

                return !$isBusy;
            })
            ->map(function ($hall) use ($hallBusy) {
                $busyMinutes = $hallBusy
                    ->filter(fn($b) => $b['hall_id'] === $hall['hall_id'])
                    ->sum(fn($b) =>
                        Carbon::createFromFormat('H:i', $b['start_time'])
                            ->diffInMinutes(Carbon::createFromFormat('H:i', $b['end_time']))
                    );

                return [...$hall, 'busy_minutes' => $busyMinutes];
            })
            ->sortBy('busy_minutes')
            ->values();
    }
}
