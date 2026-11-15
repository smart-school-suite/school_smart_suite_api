<?php

namespace App\Services\ResitTimetable\Support;

use App\Models\ResitTimetable\ResitInvigilator;
use Illuminate\Support\Collection;

/**
 * What other active timetables (exams and other resit exams) already commit
 * during a resit exam's period: halls, invigilators, and the resit candidates
 * themselves when they sit another exam then.
 */
class ResitActiveCommitments
{
    public function __construct(private ActiveExamSessions $sessions) {}

    /**
     * @param Collection<ResitInvigilator> $invigilators this resit exam's invigilators
     * @param string[] $studentIds every candidate of this resit exam
     * @return array{hall_busy_periods: array, invigilator_busy_periods: array, candidate_busy_periods: array}
     */
    public function busyPeriodsForGeneration(
        string $schoolBranchId,
        string $resitExamId,
        string $startDate,
        string $endDate,
        array $hallIds,
        Collection $invigilators,
        array $studentIds,
    ): array {
        $others = $this->sessions->all($schoolBranchId, $startDate, $endDate, null, $resitExamId, withStudents: true);

        $personToInvigilator = $invigilators->mapWithKeys(fn(ResitInvigilator $invigilator) => [
            ActiveExamSessions::personKey($invigilator->actorable_type, $invigilator->actorable_id) => $invigilator->id,
        ]);
        $hallIds = array_flip($hallIds);
        $ourStudents = array_flip(array_map('strval', $studentIds));

        $hallBusy = [];
        $invigilatorBusy = [];
        $candidateBusy = [];

        foreach ($others as $session) {
            $window = [
                'date' => $session['date'],
                'start_time' => $session['start_time'],
                'end_time' => $session['end_time'],
                'course_id' => $session['course_id'],
                'source' => 'other_exam',
            ];

            foreach ($session['halls'] ?? [] as $hallId => $candidates) {
                if (isset($hallIds[$hallId])) {
                    $hallBusy[] = ['hall_id' => $hallId, ...$window, 'candidate_count' => $candidates];
                }
            }

            foreach ($session['people'] ?? [] as $person) {
                if ($ours = $personToInvigilator->get($person)) {
                    $invigilatorBusy[] = ['invigilator_id' => $ours, ...$window];
                }
            }

            foreach ($session['student_ids'] ?? [] as $studentId) {
                if (isset($ourStudents[$studentId])) {
                    $candidateBusy[] = ['student_id' => $studentId, ...$window];
                }
            }
        }

        return [
            'hall_busy_periods' => $hallBusy,
            'invigilator_busy_periods' => $invigilatorBusy,
            'candidate_busy_periods' => $candidateBusy,
        ];
    }

    /** @return Collection active sessions of other timetables, for the activation check */
    public function otherSessions(string $schoolBranchId, string $resitExamId, string $startDate, string $endDate): Collection
    {
        return $this->sessions->all($schoolBranchId, $startDate, $endDate, null, $resitExamId, withStudents: true);
    }
}
