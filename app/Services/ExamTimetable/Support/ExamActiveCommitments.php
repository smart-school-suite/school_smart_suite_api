<?php

namespace App\Services\ExamTimetable\Support;

use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Halls and invigilators already committed to other exams during this exam's
 * period, read from those exams' active timetables.
 *
 * Invigilator rows are per exam, so the same teacher has a different
 * invigilator id in every exam. Busy periods are matched through the person
 * (actorable type and id) and given this exam's invigilator id.
 */
class ExamActiveCommitments
{
    /**
     * @param Collection<ExamInvigilator> $invigilators this exam's invigilators
     * @return array{hall_busy_periods: array, invigilator_busy_periods: array}
     */
    public function busyPeriodsForGeneration(
        string $schoolBranchId,
        string $examId,
        string $startDate,
        string $endDate,
        array $hallIds,
        Collection $invigilators,
    ): array {
        $slots = ExamTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->where('exam_id', '!=', $examId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('version.activeTimetable')
            ->with([
                'examSessionHall',
                'examSessionInvig.invigilator',
            ])
            ->get();

        $personToInvigilator = $invigilators->mapWithKeys(fn(ExamInvigilator $invigilator) => [
            $this->personKey($invigilator->actorable_type, $invigilator->actorable_id) => $invigilator->id,
        ]);
        $hallIds = array_flip($hallIds);

        $hallBusy = [];
        $invigilatorBusy = [];

        foreach ($slots as $slot) {
            $date = Carbon::parse($slot->date)->format('Y-m-d');
            $start = Time::normalize((string) $slot->getRawOriginal('start_time'));
            $end = Time::normalize((string) $slot->getRawOriginal('end_time'));

            if (!$start || !$end) {
                continue;
            }

            foreach ($slot->examSessionHall as $sessionHall) {
                if (!isset($hallIds[$sessionHall->hall_id])) {
                    continue;
                }
                $hallBusy[] = [
                    'hall_id' => $sessionHall->hall_id,
                    'date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'candidate_count' => (int) $sessionHall->candidate_count,
                    'course_id' => $slot->course_id,
                    'source' => 'other_exam',
                ];
            }

            foreach ($slot->examSessionInvig as $sessionInvigilator) {
                $person = $sessionInvigilator->invigilator;
                $ours = $person ? $personToInvigilator->get($this->personKey($person->actorable_type, $person->actorable_id)) : null;
                if (!$ours) {
                    continue;
                }
                $invigilatorBusy[] = [
                    'invigilator_id' => $ours,
                    'date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'course_id' => $slot->course_id,
                    'source' => 'other_exam',
                ];
            }
        }

        return [
            'hall_busy_periods' => $hallBusy,
            'invigilator_busy_periods' => $invigilatorBusy,
        ];
    }

    private function personKey(?string $type, ?string $id): string
    {
        return "{$type}:{$id}";
    }
}
