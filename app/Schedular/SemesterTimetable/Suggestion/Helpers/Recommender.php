<?php

namespace App\Schedular\SemesterTimetable\Suggestion\Helpers;

use App\Schedular\SemesterTimetable\Constraints\Core\ConstraintContext;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\Suggestion\DTO\SuggestionContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Recommender extends SuggestionContext
{
    // ─── Hall suggestion ──────────────────────────────────────────────────

    public function suggestHalls(array $params): Collection
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $context = ConstraintContext::fromPayload(self::$requestPayload);

        // Pre-collect placement constraints for this window
        $assignedHallIds = $this->hallsAlreadyPlaced($context, $day, $params['start_time'], $params['end_time']);

        return $context->halls()
            ->filter(function ($hall) use ($context, $day, $start, $end, $assignedHallIds) {
                $hallId = $hall['hall_id'];

                // 1. Must not conflict with hall busy slots
                $isBusy = $context->hBusySlotsForHallIdDay($hallId, $day)
                    ->some(fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                // 2. Must not already be claimed by a placement constraint
                if ($assignedHallIds->contains($hallId)) {
                    return false;
                }

                return true;
            })
            ->map(fn($hall) => [
                ...$hall,
                'busy_minutes' => $this->sumBusyMinutes(
                    $context->hBusySlotsForHallIdDay($hall['hall_id'], $day)
                ),
            ])
            ->sortBy('busy_minutes')
            ->values();
    }

    // ─── Teacher suggestion ───────────────────────────────────────────────

    public function suggestTeachers(array $params): Collection
    {
        $day   = strtolower($params['day']);
        $start = Carbon::createFromFormat('H:i', $params['start_time']);
        $end   = Carbon::createFromFormat('H:i', $params['end_time']);

        $context = ConstraintContext::fromPayload(self::$requestPayload);

        // Pre-collect placement constraints for this window
        $assignedTeacherIds = $this->teachersAlreadyPlaced($context, $day, $params['start_time'], $params['end_time']);

        return $context->teachers()
            ->filter(function ($teacher) use ($context, $day, $start, $end, $assignedTeacherIds) {
                $teacherId = $teacher['teacher_id'];

                // 1. Must not conflict with teacher busy slots
                $isBusy = $context->tBusySlotsFor($teacherId, $day)
                    ->some(fn($slot) =>
                        $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
                        $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']))
                    );

                if ($isBusy) {
                    return false;
                }

                // 2. Must fall within at least one preference window (if any defined)
                $prefs = $context->tPreferredSlotsFor($teacherId, $day);

                if ($prefs->isNotEmpty()) {
                    $withinPreference = $prefs->some(fn($pref) =>
                        $start->greaterThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['start_time'])) &&
                        $end->lessThanOrEqualTo(Carbon::createFromFormat('H:i', $pref['end_time']))
                    );

                    if (!$withinPreference) {
                        return false;
                    }
                }

                // 3. Must not already be claimed by a placement constraint
                if ($assignedTeacherIds->contains($teacherId)) {
                    return false;
                }

                return true;
            })
            ->map(fn($teacher) => [
                ...$teacher,
                'busy_minutes' => $this->sumBusyMinutes(
                    $context->tBusySlotsFor($teacher['teacher_id'], $day)
                ),
            ])
            ->sortBy('busy_minutes')
            ->values();
    }

    // ─── Time slot suggestion ─────────────────────────────────────────────

    public function suggestTimeSlot(string $day): Collection
    {
        return collect(self::$timetableGrid)
            ->filter(fn($slot) =>
                $slot->type === GridSlotDTO::TYPE_REGULAR &&
                $slot->day  === strtolower($day)
            )
            ->map(fn($slot) => [
                'day'        => $slot->day,
                'start_time' => $slot->start_time,
                'end_time'   => $slot->end_time,
            ])
            ->values();
    }

    // ─── Placement conflict resolvers ─────────────────────────────────────

    /**
     * Collects all teacher IDs already claimed at the given window
     * across requested assignments, course requested slots and teacher requested slots.
     */
    private function teachersAlreadyPlaced(
        ConstraintContext $context,
        string            $day,
        string            $startTime,
        string            $endTime
    ): Collection {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end   = Carbon::createFromFormat('H:i', $endTime);

        $conflictsSlot = fn($slot) =>
            $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']));

        // From requested assignments
        $fromAssignments = $context->requestedAssignmentsFor($day)
            ->filter($conflictsSlot)
            ->pluck('teacher_id')
            ->filter();

        // From teacher requested windows
        $fromTeacherWindows = $context->tRequestedWindowsFor($day)
            ->filter($conflictsSlot)
            ->pluck('teacher_id')
            ->filter();

        // From course requested slots — resolve teacher via course
        $fromCourseSlots = $context->cRequestedWindowsFor($day)
            ->filter($conflictsSlot)
            ->flatMap(fn($slot) => $context->teachersForCourse($slot['course_id']))
            ->filter();

        return $fromAssignments
            ->merge($fromTeacherWindows)
            ->merge($fromCourseSlots)
            ->unique()
            ->values();
    }

    /**
     * Collects all hall IDs already claimed at the given window
     * across requested assignments and hall requested slots.
     */
    private function hallsAlreadyPlaced(
        ConstraintContext $context,
        string            $day,
        string            $startTime,
        string            $endTime
    ): Collection {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end   = Carbon::createFromFormat('H:i', $endTime);

        $conflictsSlot = fn($slot) =>
            $start->lessThan(Carbon::createFromFormat('H:i', $slot['end_time'])) &&
            $end->greaterThan(Carbon::createFromFormat('H:i', $slot['start_time']));

        // From requested assignments
        $fromAssignments = $context->requestedAssignmentsFor($day)
            ->filter($conflictsSlot)
            ->pluck('hall_id')
            ->filter();

        // From hall requested windows
        $fromHallWindows = $context->hRequestedWindowsFor($day)
            ->filter($conflictsSlot)
            ->pluck('hall_id')
            ->filter();

        return $fromAssignments
            ->merge($fromHallWindows)
            ->unique()
            ->values();
    }

    // ─── Shared helper ────────────────────────────────────────────────────

    private function sumBusyMinutes(Collection $busySlots): int
    {
        return $busySlots->sum(fn($slot) =>
            Carbon::createFromFormat('H:i', $slot['start_time'])
                ->diffInMinutes(Carbon::createFromFormat('H:i', $slot['end_time']))
        );
    }
}
