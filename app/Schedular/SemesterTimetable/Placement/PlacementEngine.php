<?php

namespace App\Schedular\SemesterTimetable\Placement;

use App\Schedular\SemesterTimetable\Core\State;
use App\Schedular\SemesterTimetable\DTO\GridSlotDTO;
use App\Schedular\SemesterTimetable\DTO\TimetableContext;
use App\Schedular\SemesterTimetable\Placement\Contracts\ScorerInterface;
use App\Schedular\SemesterTimetable\Placement\Indexes\PlacementIndex;
use App\Schedular\SemesterTimetable\Placement\Scoring\LoadBasedScorer;
use App\Schedular\SemesterTimetable\Placement\Support\TimeHelper;

class PlacementEngine extends TimetableContext
{
    private ScorerInterface $scorer;

    public function __construct(?ScorerInterface $scorer = null)
    {
        $this->scorer = $scorer ?? new LoadBasedScorer();
    }

    // ─── Entry point ──────────────────────────────────────────────────────

    public function place(State $state, array $requestPayload): void
    {
        $index = PlacementIndex::fromPayload($requestPayload);

        foreach ($state->grid as $slot) {
            if (!$slot->isRegular()) {
                continue;
            }

            $candidates = $this->buildCandidates($slot, $index);

            if (empty($candidates)) {
                continue;
            }

            $best = $this->selectBest($candidates, $slot->day, $index);

            $this->applyPlacement($slot, $best, $index);
        }
    }

    // ─── Candidates ───────────────────────────────────────────────────────

    /**
     * Returns every valid (course_id, teacher_id, hall_id) triple for the slot.
     *
     * Hard gates in order:
     *   1. Teacher falls within their preferred window (if any defined)
     *   2. Teacher has no conflict with their busy periods
     *   3. Hall has no conflict with its busy periods
     *
     * @return array[]
     */
    public function buildCandidates(GridSlotDTO $slot, PlacementIndex $index): array
    {
        $slotStart  = TimeHelper::toMinutes($slot->start_time);
        $slotEnd    = TimeHelper::toMinutes($slot->end_time);
        $day        = $slot->day;
        $candidates = [];

        foreach ($index->courseTeachers() as $courseId => $teacherIds) {

            foreach ($teacherIds as $teacherId) {

                if (!$this->teacherFitsPreference($teacherId, $day, $slotStart, $slotEnd, $index)) {
                    continue;
                }

                if ($this->hasConflict($index->teacherBusy($teacherId), $day, $slotStart, $slotEnd)) {
                    continue;
                }

                foreach ($index->halls() as $hallId => $_) {
                    if ($this->hasConflict($index->hallBusy($hallId), $day, $slotStart, $slotEnd)) {
                        continue;
                    }

                    $candidates[] = [
                        'course_id'  => $courseId,
                        'teacher_id' => $teacherId,
                        'hall_id'    => $hallId,
                    ];
                }
            }
        }

        return $candidates;
    }

    // ─── Selection ────────────────────────────────────────────────────────

    public function selectBest(array $candidates, string $day, PlacementIndex $index): array
    {
        usort(
            $candidates,
            fn($a, $b) =>
            $this->scorer->score($b, $day, $index)
                <=> $this->scorer->score($a, $day, $index)
        );

        return $candidates[0];
    }

    // ─── Commit ───────────────────────────────────────────────────────────

    public function applyPlacement(GridSlotDTO $slot, array $candidate, PlacementIndex $index): void
    {
        $slot->course_id  = $candidate['course_id'];
        $slot->teacher_id = $candidate['teacher_id'];
        $slot->hall_id    = $candidate['hall_id'];

        $start = TimeHelper::toMinutes($slot->start_time);
        $end   = TimeHelper::toMinutes($slot->end_time);

        $index->commitTeacherPeriod($candidate['teacher_id'], $slot->day, $start, $end);
        $index->commitHallPeriod($candidate['hall_id'], $slot->day, $start, $end);
    }

    // ─── Availability ─────────────────────────────────────────────────────

    public function teacherFitsPreference(
        string         $teacherId,
        string         $day,
        int            $slotStart,
        int            $slotEnd,
        PlacementIndex $index
    ): bool {
        $preferences = $index->teacherPreferences($teacherId);

        if (TimetableContext::isWithoutPreference()) {
            return true;
        }
        if (empty($preferences)) {
            return true; // no preference defined → all slots valid
        }

        foreach ($preferences as $pref) {
            if (
                $pref['day'] === $day &&
                $slotStart >= $pref['start'] &&
                $slotEnd   <= $pref['end']
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasConflict(array $busyPeriods, string $day, int $slotStart, int $slotEnd): bool
    {
        foreach ($busyPeriods as $period) {
            if (
                $period['day'] === $day &&
                TimeHelper::overlaps($slotStart, $slotEnd, $period['start'], $period['end'])
            ) {
                return true;
            }
        }

        return false;
    }
}
